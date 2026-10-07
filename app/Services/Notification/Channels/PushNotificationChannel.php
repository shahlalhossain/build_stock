<?php

namespace App\Services\Notification\Channels;

use App\Models\NotificationReceiver;
use App\Models\UserDeviceToken;
use App\Services\Notification\NotificationResult;
use App\Services\Notification\Providers\PushProvider;
use App\Services\Notification\TemplateRenderer;

/**
 * The push channel. It knows how to push to a user's devices, and nothing about
 * HOW the outside push service works (that is the PushProvider's job).
 */
class PushNotificationChannel implements NotificationChannelInterface
{
    /**
     * Keeps the provider that really delivers the push and the text renderer.
     */
    public function __construct(
        protected PushProvider $provider,
        protected TemplateRenderer $renderer,
    ) {}

    /**
     * Pushes the notification to every active device of the receiver's user.
     * It succeeds if at least one device was reached.
     */
    public function send(NotificationReceiver $receiver): NotificationResult
    {
        $devices = UserDeviceToken::active()->where('user_id', $receiver->user_id)->get();

        if ($devices->isEmpty()) {
            return NotificationResult::failure('The user has no active device registered for push.', retryable: false, providerStatus: 'no_device');
        }

        $content = $this->renderer->renderForReceiver($receiver);
        $title = $content['title'];
        $body = $content['body'];
        $data = $this->buildAppData($receiver);

        $summaries = [];
        $successId = null;
        $errors = [];
        $canRetry = false;

        foreach ($devices as $device) {
            $result = $this->provider->send($device, $title, $body, $data);

            $summaries[] = $this->summarise($device, $result);

            if ($result->successful) {
                $successId ??= $result->providerMessageId;
                $device->update(['last_used_at' => now()]);

                continue;
            }

            $errors[] = str_replace($device->token, $device->maskedToken(), (string) $result->errorMessage);

            if ($result->providerStatus === NotificationResult::STATUS_INVALID_TOKEN) {
                $device->update(['is_active' => false]);
            } elseif ($result->retryable) {
                $canRetry = true;
            }
        }

        if ($successId !== null || count($errors) < $devices->count()) {
            return NotificationResult::success($successId, 'sent', ['devices' => $summaries]);
        }

        return NotificationResult::failure(implode(' | ', $errors), $canRetry, ['devices' => $summaries]);
    }

    /**
     * The extra values sent along with the push (so the app can open the right page).
     *
     * @return array<string, mixed>
     */
    protected function buildAppData(NotificationReceiver $receiver): array
    {
        $message = $receiver->message;

        return array_filter([
            'notification_id' => $message->id,
            'event_code' => $message->event_code,
            'url' => $message->data['url'] ?? null,
        ], fn ($value) => $value !== null);
    }

    /**
     * A short, secret-free note about what happened on one device (stored in the delivery history).
     *
     * @return array<string, mixed>
     */
    protected function summarise(UserDeviceToken $device, NotificationResult $result): array
    {
        return [
            'device' => $device->maskedToken(),
            'platform' => $device->platform,
            'status' => $result->providerStatus,
            'provider_message_id' => $result->providerMessageId,
        ];
    }
}
