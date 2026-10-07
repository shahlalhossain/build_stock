<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\Channels\PushNotificationChannel;
use App\Services\Notification\NotificationResult;
use App\Services\Notification\Providers\LogPushProvider;
use App\Services\Notification\Providers\PushProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PushChannelTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationChannel $push;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->push = NotificationChannel::factory()->create(['code' => 'push', 'driver' => 'push']);
        $this->user = User::factory()->create();
    }

    protected function addDevice(string $token, bool $active = true): UserDeviceToken
    {
        return $this->user->deviceTokens()->create(['token' => $token, 'is_active' => $active]);
    }

    protected function makeReceiver(bool $withSetting = true): NotificationReceiver
    {
        $setting = $withSetting ? NotificationSetting::factory()->create() : null;

        if ($setting) {
            NotificationTemplate::create([
                'notification_setting_id' => $setting->id,
                'channel_id' => $this->push->id,
                'title' => 'Brand Created',
                'body' => '{{brand_name}} created by {{actor_name}}.',
                'variables' => ['brand_name'],
            ]);
        }

        $message = NotificationMessage::factory()->create([
            'notification_setting_id' => $setting?->id,
            'event_code' => 'brand.created',
            'title' => 'Saved title',
            'message_body' => 'Saved body',
            'data' => ['brand_name' => 'ABC', 'actor_name' => 'John', 'url' => '/brand/5'],
        ]);

        return NotificationReceiver::factory()->create([
            'notification_message_id' => $message->id,
            'user_id' => $this->user->id,
            'channel_id' => $this->push->id,
        ]);
    }

    /**
     * Replaces the real provider with one that gives scripted answers and records calls.
     *
     * @param  array<string, NotificationResult>  $answersByToken
     */
    protected function fakeProvider(array $answersByToken): object
    {
        $fake = new class($answersByToken) implements PushProvider
        {
            public array $calls = [];

            public function __construct(public array $answers) {}

            public function send(UserDeviceToken $device, string $title, string $body, array $data = []): NotificationResult
            {
                $this->calls[] = compact('title', 'body', 'data') + ['token' => $device->token];

                return $this->answers[$device->token];
            }
        };

        $this->app->instance(PushProvider::class, $fake);

        return $fake;
    }

    public function test_the_push_driver_is_registered_and_the_default_provider_is_the_log_provider(): void
    {
        $this->assertInstanceOf(PushNotificationChannel::class, app(ChannelManager::class)->driver('push'));
        $this->assertInstanceOf(LogPushProvider::class, app(PushProvider::class));
    }

    public function test_it_sends_the_channel_template_text_to_the_users_device(): void
    {
        $this->addDevice('token-aaaaaaaa');
        $fake = $this->fakeProvider(['token-aaaaaaaa' => NotificationResult::success('m-1')]);

        $result = app(PushNotificationChannel::class)->send($this->makeReceiver());

        $this->assertTrue($result->successful);
        $this->assertSame('m-1', $result->providerMessageId);
        $this->assertSame('Brand Created', $fake->calls[0]['title']);
        $this->assertSame('ABC created by John.', $fake->calls[0]['body']);
        $this->assertSame('/brand/5', $fake->calls[0]['data']['url']);
        $this->assertNotNull(UserDeviceToken::first()->last_used_at);
    }

    public function test_a_manual_notification_uses_the_text_saved_on_the_notification(): void
    {
        $this->addDevice('token-aaaaaaaa');
        $fake = $this->fakeProvider(['token-aaaaaaaa' => NotificationResult::success('m-1')]);

        app(PushNotificationChannel::class)->send($this->makeReceiver(withSetting: false));

        $this->assertSame('Saved title', $fake->calls[0]['title']);
        $this->assertSame('Saved body', $fake->calls[0]['body']);
    }

    public function test_a_user_without_an_active_device_gets_a_permanent_failure(): void
    {
        $this->addDevice('token-off-1111', active: false);
        $fake = $this->fakeProvider([]);

        $result = app(PushNotificationChannel::class)->send($this->makeReceiver());

        $this->assertFalse($result->successful);
        $this->assertFalse($result->retryable);
        $this->assertSame([], $fake->calls);
    }

    public function test_an_invalid_token_is_switched_off_and_is_not_retried(): void
    {
        $device = $this->addDevice('token-bad-123456');
        $this->fakeProvider(['token-bad-123456' => NotificationResult::failure(
            'Bad token token-bad-123456', retryable: false, providerStatus: NotificationResult::STATUS_INVALID_TOKEN
        )]);

        $result = app(PushNotificationChannel::class)->send($this->makeReceiver());

        $this->assertFalse($result->successful);
        $this->assertFalse($result->retryable);
        $this->assertFalse($device->fresh()->is_active);
        $this->assertStringNotContainsString('token-bad-123456', $result->errorMessage);
    }

    public function test_a_temporary_provider_error_can_be_retried(): void
    {
        $device = $this->addDevice('token-slow-99999');
        $this->fakeProvider(['token-slow-99999' => NotificationResult::failure('Service unavailable')]);

        $result = app(PushNotificationChannel::class)->send($this->makeReceiver());

        $this->assertFalse($result->successful);
        $this->assertTrue($result->retryable);
        $this->assertTrue($device->fresh()->is_active);
    }

    public function test_it_succeeds_when_at_least_one_device_is_reached_and_hides_full_tokens(): void
    {
        $this->addDevice('token-good-111111');
        $this->addDevice('token-gone-222222');
        $this->fakeProvider([
            'token-good-111111' => NotificationResult::success('m-9'),
            'token-gone-222222' => NotificationResult::failure('Gone', false, [], NotificationResult::STATUS_INVALID_TOKEN),
        ]);

        $result = app(PushNotificationChannel::class)->send($this->makeReceiver());

        $this->assertTrue($result->successful);
        $this->assertCount(2, $result->providerResponse['devices']);
        $this->assertStringNotContainsString('token-good-111111', json_encode($result->providerResponse));
        $this->assertStringContainsString('***111111', json_encode($result->providerResponse));
    }

    public function test_the_log_provider_only_writes_the_hidden_token_to_the_log(): void
    {
        Log::spy();
        $device = $this->addDevice('token-log-abcdef');

        $result = (new LogPushProvider)->send($device, 'Hi', 'Body', ['url' => '/x']);

        $this->assertTrue($result->successful);
        Log::shouldHaveReceived('info')->withArgs(fn ($message, $context) => $context['device'] === '***abcdef'
            && ! str_contains(json_encode($context), 'token-log-abcdef'))->once();
    }
}
