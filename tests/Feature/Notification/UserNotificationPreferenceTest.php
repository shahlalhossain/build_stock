<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
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
use App\Services\Notification\NotificationService;
use App\Services\Notification\ResponseSanitizer;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UserNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationChannel $push;

    protected NotificationChannel $sms;

    protected NotificationSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->push = NotificationChannel::factory()->create(['code' => 'push', 'name' => 'Push', 'sort_order' => 1]);
        $this->sms = NotificationChannel::factory()->create(['code' => 'sms', 'name' => 'SMS', 'sort_order' => 2]);

        $this->setting = NotificationSetting::factory()->create(['event_code' => 'brand.created']);
        foreach ([$this->push, $this->sms] as $channel) {
            $this->setting->channels()->attach($channel->id, ['is_active' => true]);
            NotificationTemplate::create(['notification_setting_id' => $this->setting->id, 'channel_id' => $channel->id, 'title' => 'T', 'body' => 'B']);
        }
    }

    protected function receiverUser(): User
    {
        $user = User::factory()->create();
        $this->setting->receiverRules()->create(['receiver_type' => NotificationSettingReceiver::TYPE_USER, 'receiver_value' => (string) $user->id]);

        return $user;
    }

    protected function switchOff(User $user, NotificationChannel $channel): void
    {
        UserNotificationPreference::create(['user_id' => $user->id, 'channel_id' => $channel->id, 'is_enabled' => false]);
    }

    // ---------- creating receivers ----------

    public function test_channels_are_on_by_default(): void
    {
        $user = $this->receiverUser();

        $message = app(NotificationService::class)->createAutomaticNotification('brand.created');

        $this->assertEqualsCanonicalizing([$this->push->id, $this->sms->id], $message->receivers->where('user_id', $user->id)->pluck('channel_id')->all());
    }

    public function test_a_disabled_channel_is_skipped_but_other_channels_are_still_used(): void
    {
        $user = $this->receiverUser();
        $other = $this->receiverUser();
        $this->switchOff($user, $this->sms);

        $message = app(NotificationService::class)->createAutomaticNotification('brand.created');

        $this->assertSame([$this->push->id], $message->receivers->where('user_id', $user->id)->pluck('channel_id')->all());
        $this->assertCount(2, $message->receivers->where('user_id', $other->id));
    }

    public function test_an_explicitly_enabled_channel_is_delivered(): void
    {
        $user = $this->receiverUser();
        UserNotificationPreference::create(['user_id' => $user->id, 'channel_id' => $this->sms->id, 'is_enabled' => true]);

        $message = app(NotificationService::class)->createAutomaticNotification('brand.created');

        $this->assertCount(2, $message->receivers);
    }

    public function test_no_notification_is_kept_when_every_user_switched_every_channel_off(): void
    {
        $user = $this->receiverUser();
        $this->switchOff($user, $this->push);
        $this->switchOff($user, $this->sms);

        $this->assertNull(app(NotificationService::class)->createAutomaticNotification('brand.created'));
        $this->assertSame(0, NotificationMessage::count());
        Queue::assertNothingPushed();
    }

    public function test_a_manual_notification_also_respects_preferences(): void
    {
        $user = User::factory()->create();
        $this->switchOff($user, $this->push);

        $message = app(NotificationService::class)->createManualNotification('T', 'B', [$user->id], [$this->push->id, $this->sms->id]);

        $this->assertSame([$this->sms->id], $message->receivers->pluck('channel_id')->all());
    }

    // ---------- the job re-checks ----------

    public function test_the_job_cancels_a_delivery_if_the_user_switched_the_channel_off_after_it_was_queued(): void
    {
        $user = User::factory()->create();
        $message = NotificationMessage::factory()->create();
        $receiver = NotificationReceiver::factory()->create([
            'notification_message_id' => $message->id,
            'user_id' => $user->id,
            'channel_id' => $this->push->id,
            'status' => NotificationReceiverStatus::Queued,
        ]);
        $this->switchOff($user, $this->push);

        (new SendNotificationJob($receiver->id))->handle(app(ChannelManager::class), app(NotificationService::class), app(ResponseSanitizer::class));

        $receiver = $receiver->fresh();
        $this->assertSame(NotificationReceiverStatus::Cancelled, $receiver->status);
        $this->assertSame(0, $receiver->attempts);
        $this->assertSame('The user switched this channel off.', $receiver->logs()->first()->message);
    }

    // ---------- service ----------

    public function test_the_service_lists_only_active_channels_with_the_users_choice(): void
    {
        $user = User::factory()->create();
        $this->switchOff($user, $this->sms);
        NotificationChannel::factory()->create(['is_active' => false]);

        $list = app(UserNotificationPreferenceService::class)->channelsFor($user);

        $this->assertSame(['Push' => true, 'SMS' => false], $list->pluck('is_enabled', 'name')->all());
    }

    // ---------- endpoint and page ----------

    public function test_a_user_can_switch_a_channel_off_and_on(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson(route('notification-preference.update'), ['channel_id' => $this->push->id, 'is_enabled' => 0])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('user_notification_preferences', ['user_id' => $user->id, 'channel_id' => $this->push->id, 'is_enabled' => false]);

        $this->actingAs($user)->putJson(route('notification-preference.update'), ['channel_id' => $this->push->id, 'is_enabled' => 1])->assertOk();
        $this->assertSame(1, UserNotificationPreference::count());
        $this->assertDatabaseHas('user_notification_preferences', ['user_id' => $user->id, 'is_enabled' => true]);
    }

    public function test_a_user_only_changes_their_own_preference(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->putJson(route('notification-preference.update'), ['channel_id' => $this->push->id, 'is_enabled' => 0])->assertOk();

        $this->assertTrue(app(UserNotificationPreferenceService::class)->isEnabled($other, $this->push->id));
        $this->assertFalse(app(UserNotificationPreferenceService::class)->isEnabled($me, $this->push->id));
    }

    public function test_bad_input_and_unavailable_channels_are_rejected_and_guests_are_blocked(): void
    {
        $user = User::factory()->create();
        $off = NotificationChannel::factory()->create(['is_active' => false]);

        $this->actingAs($user)->putJson(route('notification-preference.update'), ['channel_id' => 99999, 'is_enabled' => 'maybe'])
            ->assertStatus(422)->assertJsonValidationErrors(['channel_id', 'is_enabled']);
        $this->actingAs($user)->putJson(route('notification-preference.update'), ['channel_id' => $off->id, 'is_enabled' => 0])
            ->assertStatus(422)->assertJson(['success' => false]);

        auth()->logout();
        $this->putJson(route('notification-preference.update'), ['channel_id' => $this->push->id, 'is_enabled' => 0])->assertUnauthorized();
    }

    public function test_the_profile_page_shows_the_switches(): void
    {
        $user = User::factory()->create();
        $this->switchOff($user, $this->sms);

        $response = $this->actingAs($user)->get(route('profile'))->assertOk();

        $html = $response->assertSee('Notification Preferences')->getContent();

        $this->assertMatchesRegularExpression('/id="notification-channel-'.$this->push->id.'"[^>]*\schecked/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="notification-channel-'.$this->sms->id.'"[^>]*\schecked/s', $html);
    }
}
