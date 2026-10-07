<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\Channels\NotificationChannelInterface;
use App\Services\Notification\NotificationResult;
use App\Services\Notification\NotificationService;
use App\Services\Notification\ResponseSanitizer;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotificationRetryTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationReceiver $receiver;

    /** @var array<int, NotificationResult> Answers the fake channel gives, one per attempt. */
    public array $answers = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['notification.retry.max_attempts' => 3, 'notification.retry.backoff' => [60, 300]]);

        $channel = NotificationChannel::factory()->create(['driver' => 'fake']);
        $this->receiver = NotificationReceiver::factory()->create([
            'notification_message_id' => NotificationMessage::factory()->create()->id,
            'channel_id' => $channel->id,
            'status' => NotificationReceiverStatus::Queued,
        ]);

        $test = $this;
        $manager = new ChannelManager;
        $manager->extend('fake', fn () => new class($test) implements NotificationChannelInterface
        {
            public function __construct(protected NotificationRetryTest $test) {}

            public function send(NotificationReceiver $receiver): NotificationResult
            {
                return array_shift($this->test->answers);
            }
        });
        $this->app->instance(ChannelManager::class, $manager);
    }

    /**
     * Runs one attempt. $expectedDelay is the number of seconds the job must ask the queue to wait (null = no retry).
     */
    protected function attempt(?int $expectedDelay = null): void
    {
        $job = new SendNotificationJob($this->receiver->id);

        $queueJob = Mockery::mock(Job::class);
        $expectedDelay === null
            ? $queueJob->shouldNotReceive('release')
            : $queueJob->shouldReceive('release')->once()->with($expectedDelay);
        $job->setJob($queueJob);

        $job->handle(app(ChannelManager::class), app(NotificationService::class), app(ResponseSanitizer::class));
    }

    public function test_a_temporary_failure_is_retried_later_with_the_first_backoff(): void
    {
        $this->answers = [NotificationResult::failure('Service unavailable')];

        $this->attempt(expectedDelay: 60);

        $receiver = $this->receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Queued, $receiver->status);
        $this->assertSame(1, $receiver->attempts);
        $this->assertSame('Service unavailable', $receiver->error_message);
        $this->assertEqualsWithDelta(now()->addSeconds(60)->timestamp, $receiver->next_retry_at->timestamp, 5);
        $this->assertSame(['processing', 'retry'], $receiver->logs()->orderBy('id')->pluck('event')->all());
        $this->assertSame(NotificationStatus::Processing, $receiver->message->fresh()->status);
    }

    public function test_the_wait_grows_and_after_three_attempts_the_delivery_fails_for_good(): void
    {
        $this->answers = [
            NotificationResult::failure('Down 1'),
            NotificationResult::failure('Down 2'),
            NotificationResult::failure('Down 3'),
        ];

        $this->attempt(expectedDelay: 60);
        $this->attempt(expectedDelay: 300);
        $this->attempt(expectedDelay: null);

        $receiver = $this->receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Failed, $receiver->status);
        $this->assertSame(3, $receiver->attempts);
        $this->assertNull($receiver->next_retry_at);
        $this->assertNotNull($receiver->failed_at);
        $this->assertStringContainsString('Gave up after 3 attempts', $receiver->error_message);
        $this->assertStringContainsString('Down 3', $receiver->error_message);
        $this->assertSame(['retry', 'retry', 'failed'], $receiver->logs()->whereIn('event', ['retry', 'failed'])->orderBy('id')->pluck('event')->all());
        $this->assertSame(NotificationStatus::Failed, $receiver->message->fresh()->status);
    }

    public function test_a_permanent_error_is_not_retried(): void
    {
        $this->answers = [NotificationResult::failure('Invalid device', retryable: false)];

        $this->attempt(expectedDelay: null);

        $receiver = $this->receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Failed, $receiver->status);
        $this->assertSame(1, $receiver->attempts);
        $this->assertSame('Invalid device', $receiver->error_message);
    }

    public function test_a_retry_that_works_clears_the_error_and_marks_the_delivery_sent(): void
    {
        $this->answers = [NotificationResult::failure('Blip'), NotificationResult::success('ok-1')];

        $this->attempt(expectedDelay: 60);
        $this->attempt(expectedDelay: null);

        $receiver = $this->receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Sent, $receiver->status);
        $this->assertSame(2, $receiver->attempts);
        $this->assertNull($receiver->error_message);
        $this->assertNull($receiver->next_retry_at);
        $this->assertSame(NotificationStatus::Completed, $receiver->message->fresh()->status);
    }

    public function test_the_job_limits_match_the_config(): void
    {
        $job = new SendNotificationJob(1);

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300], $job->backoff());
    }

    public function test_when_the_queue_gives_up_the_delivery_is_marked_failed(): void
    {
        (new SendNotificationJob($this->receiver->id))->failed(new Exception('Database went away'));

        $receiver = $this->receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Failed, $receiver->status);
        $this->assertStringContainsString('Database went away', $receiver->error_message);
        $this->assertSame(NotificationStatus::Failed, $receiver->message->fresh()->status);
    }

    public function test_the_give_up_hook_does_not_touch_a_delivery_that_already_succeeded(): void
    {
        $this->receiver->update(['status' => NotificationReceiverStatus::Sent]);

        (new SendNotificationJob($this->receiver->id))->failed(new Exception('late error'));

        $this->assertSame(NotificationReceiverStatus::Sent, $this->receiver->fresh()->status);
    }

    public function test_secrets_are_removed_from_provider_answers_and_errors_before_saving(): void
    {
        $this->answers = [NotificationResult::failure(
            'Request failed with Authorization: Bearer abc.def-123 and api_key=SECRET123',
            retryable: false,
            providerResponse: ['status' => 400, 'api_key' => 'SECRET123', 'headers' => ['Authorization' => 'Bearer abc'], 'note' => 'password=hunter2'],
        )];

        $this->attempt(expectedDelay: null);

        $receiver = $this->receiver->fresh();
        $stored = json_encode([$receiver->error_message, $receiver->provider_response, $receiver->logs->map(fn ($log) => [$log->message, $log->response_payload])->all()]);
        $this->assertStringNotContainsString('abc.def-123', $stored);
        $this->assertStringNotContainsString('SECRET123', $stored);
        $this->assertStringNotContainsString('hunter2', $stored);
        $this->assertSame(400, $receiver->provider_response['status']);
        $this->assertSame('[hidden]', $receiver->provider_response['api_key']);
    }

    public function test_the_sanitizer_cuts_very_long_text(): void
    {
        $clean = (new ResponseSanitizer)->sanitizeText(str_repeat('a', 5000));

        $this->assertSame(2000, mb_strlen($clean));
    }
}
