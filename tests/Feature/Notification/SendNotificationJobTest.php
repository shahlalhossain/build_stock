<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\User;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\Channels\NotificationChannelInterface;
use App\Services\Notification\NotificationResult;
use App\Services\Notification\NotificationService;
use App\Services\Notification\ResponseSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationChannel $channel;

    protected NotificationMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->channel = NotificationChannel::factory()->create(['code' => 'fake', 'driver' => 'fake']);
        $this->message = NotificationMessage::factory()->create();
    }

    protected function makeReceiver(array $attributes = []): NotificationReceiver
    {
        return NotificationReceiver::factory()->create($attributes + [
            'notification_message_id' => $this->message->id,
            'channel_id' => $this->channel->id,
            'status' => NotificationReceiverStatus::Queued,
        ]);
    }

    protected function useChannel(callable $send): void
    {
        $channel = new class($send) implements NotificationChannelInterface
        {
            public function __construct(protected $send) {}

            public function send(NotificationReceiver $receiver): NotificationResult
            {
                return ($this->send)($receiver);
            }
        };

        $manager = new ChannelManager;
        $manager->extend('fake', fn () => $channel);
        $this->app->instance(ChannelManager::class, $manager);
    }

    protected function runJob(NotificationReceiver $receiver): void
    {
        (new SendNotificationJob($receiver->id))->handle(app(ChannelManager::class), app(NotificationService::class), app(ResponseSanitizer::class));
    }

    // ---------- dispatching ----------

    public function test_dispatching_queues_one_job_per_pending_delivery(): void
    {
        Queue::fake();
        $a = $this->makeReceiver(['status' => NotificationReceiverStatus::Pending]);
        $b = $this->makeReceiver(['status' => NotificationReceiverStatus::Pending]);

        $count = app(NotificationService::class)->dispatchJobs($this->message);

        $this->assertSame(2, $count);
        Queue::assertPushed(SendNotificationJob::class, 2);
        Queue::assertPushed(SendNotificationJob::class, fn ($job) => $job->receiverId === $a->id && $job->afterCommit === true);
        $this->assertSame(NotificationReceiverStatus::Queued, $b->fresh()->status);
        $this->assertNotNull($b->fresh()->queued_at);
        $this->assertSame('queued', $b->logs()->first()->event);
        $this->assertSame(NotificationStatus::Processing, $this->message->fresh()->status);
    }

    public function test_dispatching_works_in_chunks_for_many_deliveries(): void
    {
        Queue::fake();
        config(['notification.chunk_size' => 2]);
        foreach (range(1, 5) as $ignored) {
            $this->makeReceiver(['status' => NotificationReceiverStatus::Pending]);
        }

        $this->assertSame(5, app(NotificationService::class)->dispatchJobs($this->message));
        Queue::assertPushed(SendNotificationJob::class, 5);
    }

    public function test_a_scheduled_notification_is_delayed(): void
    {
        Queue::fake();
        $this->message->update(['scheduled_at' => now()->addHour()]);
        $this->makeReceiver(['status' => NotificationReceiverStatus::Pending]);

        app(NotificationService::class)->dispatchJobs($this->message);

        Queue::assertPushed(SendNotificationJob::class, fn ($job) => $job->delay !== null);
    }

    // ---------- running the job ----------

    public function test_a_successful_send_marks_the_delivery_sent_and_stores_the_provider_answer(): void
    {
        $this->useChannel(fn () => NotificationResult::success('prov-1', 'sent', ['devices' => 1]));
        $receiver = $this->makeReceiver();

        $this->runJob($receiver);

        $receiver = $receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Sent, $receiver->status);
        $this->assertSame(1, $receiver->attempts);
        $this->assertNotNull($receiver->sent_at);
        $this->assertSame('prov-1', $receiver->provider_message_id);
        $this->assertSame(['devices' => 1], $receiver->provider_response);
        $this->assertSame(['processing', 'provider_response', 'sent'], $receiver->logs()->orderBy('id')->pluck('event')->all());
        $this->assertSame(NotificationStatus::Completed, $this->message->fresh()->status);
    }

    public function test_a_failed_send_marks_the_delivery_failed_with_the_error(): void
    {
        $this->useChannel(fn () => NotificationResult::failure('Provider said no', false, ['code' => 400]));
        $receiver = $this->makeReceiver();

        $this->runJob($receiver);

        $receiver = $receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Failed, $receiver->status);
        $this->assertSame('Provider said no', $receiver->error_message);
        $this->assertNotNull($receiver->failed_at);
        $this->assertSame(['code' => 400], $receiver->provider_response);
        $this->assertSame('failed', $receiver->logs()->orderByDesc('id')->first()->event);
        $this->assertSame(NotificationStatus::Failed, $this->message->fresh()->status);
    }

    public function test_an_unexpected_crash_is_recorded_and_the_delivery_waits_for_a_retry(): void
    {
        $this->useChannel(function () {
            throw new RuntimeException('boom');
        });
        $receiver = $this->makeReceiver();

        $this->runJob($receiver);

        $this->assertSame(NotificationReceiverStatus::Queued, $receiver->fresh()->status);
        $this->assertStringContainsString('boom', $receiver->fresh()->error_message);
        $this->assertNotNull($receiver->fresh()->next_retry_at);
    }

    public function test_the_notification_stays_processing_until_every_delivery_is_finished_then_becomes_partial(): void
    {
        $this->useChannel(fn (NotificationReceiver $receiver) => $receiver->user->name === 'Good User'
            ? NotificationResult::success('ok')
            : NotificationResult::failure('no', retryable: false));
        $good = $this->makeReceiver(['user_id' => User::factory()->create(['name' => 'Good User'])->id]);
        $bad = $this->makeReceiver(['user_id' => User::factory()->create(['name' => 'Bad User'])->id]);

        $this->runJob($good);
        $this->assertSame(NotificationStatus::Processing, $this->message->fresh()->status);

        $this->runJob($bad);
        $this->assertSame(NotificationStatus::Partial, $this->message->fresh()->status);
    }

    public function test_a_delivery_is_cancelled_when_the_channel_user_or_notification_is_no_longer_active(): void
    {
        $this->useChannel(fn () => NotificationResult::success('never'));

        $inactiveUser = $this->makeReceiver(['user_id' => User::factory()->create(['is_active' => false])->id]);
        $this->runJob($inactiveUser);
        $this->assertSame(NotificationReceiverStatus::Cancelled, $inactiveUser->fresh()->status);
        $this->assertSame('skipped', $inactiveUser->logs()->first()->event);

        $this->channel->update(['is_active' => false]);
        $offChannel = $this->makeReceiver();
        $this->runJob($offChannel);
        $this->assertSame(NotificationReceiverStatus::Cancelled, $offChannel->fresh()->status);

        $this->channel->update(['is_active' => true]);
        $this->message->update(['status' => NotificationStatus::Cancelled]);
        $cancelled = $this->makeReceiver();
        $this->runJob($cancelled);
        $this->assertSame(NotificationReceiverStatus::Cancelled, $cancelled->fresh()->status);
        $this->assertSame(0, $cancelled->fresh()->attempts);
    }

    public function test_an_already_finished_delivery_is_not_sent_again(): void
    {
        $calls = 0;
        $this->useChannel(function () use (&$calls) {
            $calls++;

            return NotificationResult::success('x');
        });
        $receiver = $this->makeReceiver(['status' => NotificationReceiverStatus::Sent]);

        $this->runJob($receiver);

        $this->assertSame(0, $calls);
        $this->assertSame(0, $receiver->fresh()->attempts);
    }

    public function test_a_missing_delivery_is_ignored(): void
    {
        (new SendNotificationJob(999999))->handle(app(ChannelManager::class), app(NotificationService::class), app(ResponseSanitizer::class));

        $this->assertTrue(true);
    }
}
