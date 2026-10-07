<?php

namespace App\Jobs\Notifications;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Models\NotificationReceiver;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\NotificationResult;
use App\Services\Notification\NotificationService;
use App\Services\Notification\ResponseSanitizer;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends ONE delivery (one user on one channel) in the background.
 * It only knows the receiver's id; everything else is loaded fresh when it runs.
 *
 * Steps: check the delivery is still allowed -> mark "processing" -> send through the
 * channel -> save the result and a history line -> update the notification's overall status.
 *
 * If sending fails with a temporary problem, the job puts itself back in the queue
 * and tries again later (see config/notification.php "retry"). Permanent problems are not retried.
 */
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * How many times the queue may run this job (same number as our own attempt limit).
     */
    public int $tries;

    /**
     * Remembers which delivery to send, and makes sure the job is only queued
     * after the database transaction that created it is saved.
     */
    public function __construct(public int $receiverId)
    {
        $this->tries = (int) config('notification.retry.max_attempts', 3);
        $this->afterCommit();
    }

    /**
     * Seconds the queue waits before running this job again after an unexpected crash.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return config('notification.retry.backoff', [60, 300]);
    }

    /**
     * Runs the job (called by the queue worker).
     */
    public function handle(ChannelManager $channels, NotificationService $notifications, ResponseSanitizer $sanitizer): void
    {
        $receiver = NotificationReceiver::with(['message', 'channel', 'user'])->find($this->receiverId);

        if ($receiver === null || $receiver->status->isFinished()) {
            return;
        }

        $reason = $this->reasonToSkip($receiver);
        if ($reason !== null) {
            $this->markCancelled($receiver, $reason);
            $notifications->refreshStatus($receiver->message);

            return;
        }

        $this->markProcessing($receiver);

        $result = $this->sendThroughChannel($channels, $receiver);

        if ($result->successful) {
            $this->markSent($receiver, $result, $sanitizer);
        } elseif ($this->shouldRetry($receiver, $result)) {
            $this->markWaitingForRetry($receiver, $result, $sanitizer);
        } else {
            $this->markFailed($receiver, $result, $sanitizer);
        }

        $notifications->refreshStatus($receiver->message);
    }

    /**
     * Called by the queue when the job crashed too many times (an unexpected error
     * outside our normal handling). Makes sure the delivery ends as "failed".
     */
    public function failed(?Throwable $exception = null): void
    {
        $receiver = NotificationReceiver::with('message')->find($this->receiverId);

        if ($receiver === null || $receiver->status->isFinished()) {
            return;
        }

        $sanitizer = app(ResponseSanitizer::class);
        $message = $sanitizer->sanitizeText('Job failed: '.($exception?->getMessage() ?? 'unknown error'));

        $receiver->update([
            'status' => NotificationReceiverStatus::Failed,
            'failed_at' => now(),
            'next_retry_at' => null,
            'error_message' => $message,
        ]);
        $receiver->addLog('failed', NotificationReceiverStatus::Failed->value, $message);

        app(NotificationService::class)->refreshStatus($receiver->message);
    }

    /**
     * Gives the reason this delivery should NOT be sent any more (or null if it is fine).
     */
    protected function reasonToSkip(NotificationReceiver $receiver): ?string
    {
        return match (true) {
            $receiver->message->status === NotificationStatus::Cancelled || ! $receiver->message->is_active => 'The notification was cancelled.',
            $receiver->channel === null || ! $receiver->channel->is_active => 'The channel is no longer active.',
            $receiver->user === null || ! $receiver->user->is_active => 'The user is no longer active.',
            ! app(UserNotificationPreferenceService::class)->isEnabled($receiver->user, $receiver->channel_id) => 'The user switched this channel off.',
            default => null,
        };
    }

    /**
     * Asks the channel to send. Any unexpected error becomes a normal failed result,
     * so the delivery is always recorded.
     */
    protected function sendThroughChannel(ChannelManager $channels, NotificationReceiver $receiver): NotificationResult
    {
        try {
            return $channels->send($receiver);
        } catch (Throwable $exception) {
            Log::error("Notification delivery #{$receiver->id} crashed: ".$exception->getMessage());

            return NotificationResult::failure('Unexpected error while sending: '.$exception->getMessage());
        }
    }

    /**
     * A failed delivery is tried again only if the problem is temporary
     * AND we have not used up all attempts.
     */
    protected function shouldRetry(NotificationReceiver $receiver, NotificationResult $result): bool
    {
        return $result->retryable && $receiver->attempts < $this->tries;
    }

    /**
     * How many seconds to wait before the next attempt (longer after each failure).
     */
    protected function secondsBeforeRetry(NotificationReceiver $receiver): int
    {
        $waits = $this->backoff();
        $index = min($receiver->attempts - 1, count($waits) - 1);

        return (int) ($waits[$index] ?? 60);
    }

    /**
     * Marks the delivery as being worked on and counts the attempt.
     */
    protected function markProcessing(NotificationReceiver $receiver): void
    {
        $receiver->update([
            'status' => NotificationReceiverStatus::Processing,
            'attempts' => $receiver->attempts + 1,
        ]);

        $receiver->addLog('processing', NotificationReceiverStatus::Processing->value, 'Attempt '.$receiver->attempts);
    }

    /**
     * Saves a successful result. "Sent" means handed to the provider, not read by the user.
     */
    protected function markSent(NotificationReceiver $receiver, NotificationResult $result, ResponseSanitizer $sanitizer): void
    {
        $response = $sanitizer->sanitizeArray($result->providerResponse);

        $receiver->update([
            'status' => NotificationReceiverStatus::Sent,
            'sent_at' => now(),
            'failed_at' => null,
            'next_retry_at' => null,
            'error_message' => null,
            'provider_message_id' => $result->providerMessageId,
            'provider_status' => $result->providerStatus,
            'provider_response' => $response,
        ]);

        $receiver->addLog('provider_response', $result->providerStatus, null, null, $response);
        $receiver->addLog('sent', NotificationReceiverStatus::Sent->value);
    }

    /**
     * Keeps the delivery in the queue to try again later: saves the error and the
     * time of the next attempt, then puts this job back in the queue with a delay.
     */
    protected function markWaitingForRetry(NotificationReceiver $receiver, NotificationResult $result, ResponseSanitizer $sanitizer): void
    {
        $seconds = $this->secondsBeforeRetry($receiver);
        $error = $sanitizer->sanitizeText($result->errorMessage);
        $response = $sanitizer->sanitizeArray($result->providerResponse);

        $receiver->update([
            'status' => NotificationReceiverStatus::Queued,
            'next_retry_at' => now()->addSeconds($seconds),
            'error_message' => $error,
            'provider_status' => $result->providerStatus,
            'provider_response' => $response,
        ]);

        $receiver->addLog('retry', NotificationReceiverStatus::Queued->value, "Attempt {$receiver->attempts} failed: {$error} Retrying in {$seconds} seconds.", null, $response);

        $this->release($seconds);
    }

    /**
     * Saves a final failure with the error message.
     */
    protected function markFailed(NotificationReceiver $receiver, NotificationResult $result, ResponseSanitizer $sanitizer): void
    {
        $error = $sanitizer->sanitizeText($result->errorMessage);
        $response = $sanitizer->sanitizeArray($result->providerResponse);

        if ($result->retryable) {
            $error = "Gave up after {$receiver->attempts} attempts. Last error: {$error}";
        }

        $receiver->update([
            'status' => NotificationReceiverStatus::Failed,
            'failed_at' => now(),
            'next_retry_at' => null,
            'error_message' => $error,
            'provider_status' => $result->providerStatus,
            'provider_response' => $response,
        ]);

        $receiver->addLog('failed', NotificationReceiverStatus::Failed->value, $error, null, $response);
    }

    /**
     * Cancels the delivery without sending, and records why.
     */
    protected function markCancelled(NotificationReceiver $receiver, string $reason): void
    {
        $receiver->update(['status' => NotificationReceiverStatus::Cancelled]);

        $receiver->addLog('skipped', NotificationReceiverStatus::Cancelled->value, $reason);
    }
}
