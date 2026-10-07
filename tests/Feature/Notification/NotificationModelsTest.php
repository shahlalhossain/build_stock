<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Models\UserNotificationPreference;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_scopes_only_return_active_channels_in_order(): void
    {
        NotificationChannel::factory()->create(['code' => 'b_off', 'is_active' => false]);
        NotificationChannel::factory()->create(['code' => 'z_on', 'sort_order' => 2]);
        NotificationChannel::factory()->create(['code' => 'a_on', 'sort_order' => 1]);

        $codes = NotificationChannel::active()->ordered()->pluck('code')->all();

        $this->assertSame(['a_on', 'z_on'], $codes);
    }

    public function test_channel_code_must_be_unique(): void
    {
        NotificationChannel::factory()->create(['code' => 'push']);

        $this->expectException(QueryException::class);
        NotificationChannel::factory()->create(['code' => 'push']);
    }

    public function test_setting_can_be_found_by_event_code(): void
    {
        $setting = NotificationSetting::factory()->create(['event_code' => 'brand.created']);
        NotificationSetting::factory()->create(['event_code' => 'brand.updated']);

        $this->assertTrue(NotificationSetting::forEvent('brand.created')->first()->is($setting));
    }

    public function test_setting_active_channels_skips_switched_off_channels(): void
    {
        $setting = NotificationSetting::factory()->create();
        $on = NotificationChannel::factory()->create();
        $offOnSetting = NotificationChannel::factory()->create();
        $offGlobally = NotificationChannel::factory()->create(['is_active' => false]);

        $setting->channels()->attach($on->id, ['is_active' => true]);
        $setting->channels()->attach($offOnSetting->id, ['is_active' => false]);
        $setting->channels()->attach($offGlobally->id, ['is_active' => true]);

        $this->assertCount(3, $setting->channels);
        $this->assertSame([$on->id], $setting->activeChannels()->pluck('notification_channels.id')->all());
    }

    public function test_same_channel_cannot_be_added_twice_to_a_setting(): void
    {
        $setting = NotificationSetting::factory()->create();
        $channel = NotificationChannel::factory()->create();

        $setting->channels()->attach($channel->id);

        $this->expectException(QueryException::class);
        $setting->channels()->attach($channel->id);
    }

    public function test_only_one_template_per_setting_and_channel(): void
    {
        $setting = NotificationSetting::factory()->create();
        $channel = NotificationChannel::factory()->create();
        $row = ['notification_setting_id' => $setting->id, 'channel_id' => $channel->id, 'body' => 'Hello {{name}}'];

        $template = NotificationTemplate::create($row + ['variables' => ['name']]);

        $this->assertSame(['name'], $template->fresh()->variables);
        $this->assertTrue($setting->templates->first()->is($template));

        $this->expectException(QueryException::class);
        NotificationTemplate::create($row);
    }

    public function test_receiver_rules_belong_to_a_setting(): void
    {
        $setting = NotificationSetting::factory()->create();

        $setting->receiverRules()->create([
            'receiver_type' => NotificationSettingReceiver::TYPE_ROLE,
            'receiver_value' => 'Store Manager',
        ]);

        $this->assertSame('Store Manager', $setting->receiverRules()->first()->receiver_value);
    }

    public function test_message_casts_columns_to_enums_and_arrays(): void
    {
        $message = NotificationMessage::factory()->create(['data' => ['model' => 'Brand', 'model_id' => 25]]);
        $message = $message->fresh();

        $this->assertSame(NotificationType::Automatic, $message->type);
        $this->assertSame(NotificationStatus::Pending, $message->status);
        $this->assertSame(25, $message->data['model_id']);
    }

    public function test_message_scopes_filter_by_status_and_type(): void
    {
        NotificationMessage::factory()->create();
        NotificationMessage::factory()->create(['type' => NotificationType::Manual, 'status' => NotificationStatus::Completed]);

        $this->assertSame(1, NotificationMessage::withStatus(NotificationStatus::Completed)->count());
        $this->assertSame(1, NotificationMessage::ofType(NotificationType::Manual)->count());
    }

    public function test_receiver_links_message_user_channel_and_logs(): void
    {
        $receiver = NotificationReceiver::factory()->create(['provider_response' => ['id' => 'abc']]);
        $receiver->logs()->create(['event' => 'queued']);

        $receiver = $receiver->fresh();

        $this->assertInstanceOf(NotificationMessage::class, $receiver->message);
        $this->assertInstanceOf(User::class, $receiver->user);
        $this->assertInstanceOf(NotificationChannel::class, $receiver->channel);
        $this->assertSame(NotificationReceiverStatus::Pending, $receiver->status);
        $this->assertSame(['id' => 'abc'], $receiver->provider_response);
        $this->assertInstanceOf(NotificationLog::class, $receiver->logs->first());
        $this->assertNotNull($receiver->logs->first()->created_at);
        $this->assertCount(1, $receiver->message->receivers);
    }

    public function test_same_user_cannot_get_the_same_notification_twice_on_one_channel(): void
    {
        $receiver = NotificationReceiver::factory()->create();

        $this->expectException(QueryException::class);
        NotificationReceiver::factory()->create([
            'notification_message_id' => $receiver->notification_message_id,
            'user_id' => $receiver->user_id,
            'channel_id' => $receiver->channel_id,
        ]);
    }

    public function test_status_enums_know_when_they_are_finished(): void
    {
        $this->assertTrue(NotificationReceiverStatus::Delivered->isFinished());
        $this->assertFalse(NotificationReceiverStatus::Queued->isFinished());
        $this->assertTrue(NotificationStatus::Partial->isFinished());
        $this->assertFalse(NotificationStatus::Processing->isFinished());
    }

    public function test_user_preference_is_unique_per_user_and_channel(): void
    {
        $user = User::factory()->create();
        $channel = NotificationChannel::factory()->create();

        $user->notificationPreferences()->create(['channel_id' => $channel->id, 'is_enabled' => false]);

        $this->assertSame(0, UserNotificationPreference::enabled()->count());

        $this->expectException(QueryException::class);
        $user->notificationPreferences()->create(['channel_id' => $channel->id]);
    }

    public function test_device_token_is_hidden_in_json_and_scoped_to_active(): void
    {
        $user = User::factory()->create();
        $user->deviceTokens()->create(['token' => 'secret-token-1']);
        $user->deviceTokens()->create(['token' => 'secret-token-2', 'is_active' => false]);

        $this->assertSame(1, UserDeviceToken::active()->count());
        $this->assertArrayNotHasKey('token', $user->deviceTokens()->first()->toArray());
    }
}
