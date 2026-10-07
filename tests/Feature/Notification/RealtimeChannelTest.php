<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Events\Notifications\NotificationReceived;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\Channels\RealtimeNotificationChannel;
use App\Services\Notification\NotificationService;
use App\Services\Notification\ResponseSanitizer;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class RealtimeChannelTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationChannel $realtime;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->realtime = NotificationChannel::factory()->create(['code' => 'realtime', 'name' => 'Real-time', 'driver' => 'realtime']);
        $this->user = User::factory()->create();
    }

    protected function makeReceiver(array $data = ['url' => '/brand/7']): NotificationReceiver
    {
        $message = NotificationMessage::factory()->create(['title' => 'Brand Created', 'message_body' => 'ABC created', 'data' => $data]);

        return NotificationReceiver::factory()->create([
            'notification_message_id' => $message->id,
            'user_id' => $this->user->id,
            'channel_id' => $this->realtime->id,
            'status' => NotificationReceiverStatus::Queued,
        ]);
    }

    public function test_the_realtime_driver_is_registered(): void
    {
        $this->assertInstanceOf(RealtimeNotificationChannel::class, app(ChannelManager::class)->driver('realtime'));
    }

    public function test_sending_broadcasts_to_the_users_private_channel_with_the_board_data(): void
    {
        Event::fake([NotificationReceived::class]);
        $receiver = $this->makeReceiver();

        $result = app(RealtimeNotificationChannel::class)->send($receiver);

        $this->assertTrue($result->successful);
        $this->assertSame('sent', $result->providerStatus);
        Event::assertDispatched(NotificationReceived::class, function (NotificationReceived $event) use ($receiver) {
            $this->assertEquals(new PrivateChannel('App.Models.User.'.$this->user->id), $event->broadcastOn());
            $this->assertSame('notification.received', $event->broadcastAs());
            $data = $event->broadcastWith();
            $this->assertSame($receiver->id, $data['id']);
            $this->assertSame('Brand Created', $data['title']);
            $this->assertSame('ABC created', $data['body']);
            $this->assertSame('/brand/7', $data['url']);
            $this->assertFalse($data['is_read']);
            $this->assertSame(1, $data['unread_count']); // this new one

            return $event->userId === $this->user->id;
        });
    }

    public function test_the_unread_count_in_the_broadcast_includes_notifications_already_on_the_board(): void
    {
        Event::fake([NotificationReceived::class]);
        NotificationReceiver::factory()->create([
            'notification_message_id' => NotificationMessage::factory()->create()->id,
            'user_id' => $this->user->id,
            'channel_id' => $this->realtime->id,
            'status' => NotificationReceiverStatus::Sent,
        ]);

        app(RealtimeNotificationChannel::class)->send($this->makeReceiver());

        Event::assertDispatched(NotificationReceived::class, fn ($event) => $event->item['unread_count'] === 2);
    }

    public function test_a_broadcaster_problem_is_a_temporary_failure_that_is_retried(): void
    {
        Broadcast::extend('failing', fn () => new class extends Broadcaster
        {
            public function auth($request) {}

            public function validAuthenticationResponse($request, $result) {}

            public function broadcast(array $channels, $event, array $payload = [])
            {
                throw new RuntimeException('Pusher is down');
            }
        });
        config(['broadcasting.default' => 'failing', 'broadcasting.connections.failing' => ['driver' => 'failing']]);

        $result = app(RealtimeNotificationChannel::class)->send($this->makeReceiver());

        $this->assertFalse($result->successful);
        $this->assertTrue($result->retryable);
        $this->assertStringContainsString('Pusher is down', $result->errorMessage);
    }

    public function test_the_log_broadcaster_works_without_any_account(): void
    {
        config(['broadcasting.default' => 'log']);

        $this->assertTrue(app(RealtimeNotificationChannel::class)->send($this->makeReceiver())->successful);
    }

    public function test_the_job_marks_the_delivery_sent_so_it_appears_on_the_board(): void
    {
        config(['broadcasting.default' => 'log']);
        $receiver = $this->makeReceiver();

        (new SendNotificationJob($receiver->id))->handle(app(ChannelManager::class), app(NotificationService::class), app(ResponseSanitizer::class));

        $this->assertSame(NotificationReceiverStatus::Sent, $receiver->fresh()->status);
        $this->assertSame($receiver->id, $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json('items.0.id'));
    }

    public function test_an_event_reaches_the_board_end_to_end_through_the_queue(): void
    {
        config(['broadcasting.default' => 'log']);
        $setting = NotificationSetting::factory()->create(['event_code' => 'brand.created']);
        $setting->channels()->attach($this->realtime->id, ['is_active' => true]);
        NotificationTemplate::create(['notification_setting_id' => $setting->id, 'channel_id' => $this->realtime->id, 'title' => 'Brand Created', 'body' => '{{brand_name}} created by {{actor_name}}.', 'variables' => ['brand_name']]);
        $setting->receiverRules()->create(['receiver_type' => NotificationSettingReceiver::TYPE_USER, 'receiver_value' => (string) $this->user->id]);

        $message = app(NotificationService::class)->createAutomaticNotification('brand.created', ['brand_name' => 'ABC', 'url' => '/brand/9'], User::factory()->create(['name' => 'John']));

        $this->assertSame(NotificationStatus::Completed, $message->fresh()->status);
        $item = $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json('items.0');
        $this->assertSame('ABC created by John.', $item['body']);
        $this->assertSame('/brand/9', $item['url']);
        $this->assertFalse($item['is_read']);
    }

    // ---------- always-on channel ----------

    public function test_the_board_channel_cannot_be_switched_off_and_is_not_offered(): void
    {
        $other = NotificationChannel::factory()->create(['code' => 'push', 'name' => 'Push']);
        $service = app(UserNotificationPreferenceService::class);

        $this->assertSame(['Push'], $service->channelsFor($this->user)->pluck('name')->all());
        $this->assertTrue($service->isEnabled($this->user, $this->realtime->id));

        // Even a leftover "off" row for the board channel is ignored.
        UserNotificationPreference::create(['user_id' => $this->user->id, 'channel_id' => $this->realtime->id, 'is_enabled' => false]);
        UserNotificationPreference::create(['user_id' => $this->user->id, 'channel_id' => $other->id, 'is_enabled' => false]);

        $this->assertTrue($service->isEnabled($this->user, $this->realtime->id));
        $this->assertSame([$this->user->id.':'.$other->id => true], $service->switchedOff(collect([$this->user->id]), collect([$this->realtime->id, $other->id])));

        $this->actingAs($this->user)->putJson(route('notification-preference.update'), ['channel_id' => $this->realtime->id, 'is_enabled' => 0])
            ->assertStatus(422);
    }
}
