<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationChannel;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    protected NotificationSetting $setting;

    protected NotificationChannel $push;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->service = app(NotificationService::class);

        $this->push = NotificationChannel::factory()->create(['code' => 'push', 'driver' => 'push']);
        $this->setting = NotificationSetting::factory()->create(['event_code' => 'brand.created', 'name' => 'Brand Created']);
        $this->setting->channels()->attach($this->push->id, ['is_active' => true]);
        $this->addTemplate($this->push, 'Brand Created', '{{brand_name}} created by {{actor_name}}.');
    }

    protected function addTemplate(NotificationChannel $channel, string $title, string $body): NotificationTemplate
    {
        return NotificationTemplate::create([
            'notification_setting_id' => $this->setting->id,
            'channel_id' => $channel->id,
            'title' => $title,
            'body' => $body,
            'variables' => ['brand_name'],
        ]);
    }

    protected function receiverUsers(int $count = 2)
    {
        $users = User::factory()->count($count)->create();
        foreach ($users as $user) {
            $this->setting->receiverRules()->create([
                'receiver_type' => NotificationSettingReceiver::TYPE_USER,
                'receiver_value' => (string) $user->id,
            ]);
        }

        return $users;
    }

    public function test_an_automatic_notification_creates_the_message_and_one_receiver_per_user_and_channel(): void
    {
        $users = $this->receiverUsers(2);
        $actor = User::factory()->create(['name' => 'John']);

        $message = $this->service->createAutomaticNotification('brand.created', ['brand_name' => 'ABC Cement', 'model_id' => 25], $actor);

        $this->assertSame(NotificationType::Automatic, $message->type);
        $this->assertSame(NotificationStatus::Processing, $message->fresh()->status);
        $this->assertSame('brand.created', $message->event_code);
        $this->assertSame('Brand Created', $message->title);
        $this->assertSame('ABC Cement created by John.', $message->message_body);
        $this->assertSame(25, $message->data['model_id']);
        $this->assertSame($actor->id, $message->created_by);

        $this->assertSame(2, $message->receivers()->count());
        $this->assertEqualsCanonicalizing($users->pluck('id')->all(), $message->receivers->pluck('user_id')->all());
        $this->assertSame(NotificationReceiverStatus::Queued, $message->receivers->first()->status);
    }

    public function test_nothing_is_created_when_the_event_has_no_setting_or_it_is_switched_off(): void
    {
        $this->receiverUsers();

        $this->assertNull($this->service->createAutomaticNotification('unknown.event'));

        $this->setting->update(['is_active' => false]);
        $this->assertNull($this->service->createAutomaticNotification('brand.created'));
        $this->assertSame(0, NotificationReceiver::count());
    }

    public function test_a_channel_without_an_active_template_is_skipped(): void
    {
        $this->receiverUsers(1);
        $sms = NotificationChannel::factory()->create(['code' => 'sms', 'driver' => 'sms']);
        $this->setting->channels()->attach($sms->id, ['is_active' => true]);

        $message = $this->service->createAutomaticNotification('brand.created', ['brand_name' => 'X']);

        $this->assertSame([$this->push->id], $message->receivers->pluck('channel_id')->all());

        $this->addTemplate($sms, 'T', 'B');
        $second = $this->service->createAutomaticNotification('brand.created', ['brand_name' => 'X']);
        $this->assertSame(2, $second->receivers()->count());
    }

    public function test_switched_off_channels_are_not_used(): void
    {
        $this->receiverUsers(1);
        $this->push->update(['is_active' => false]);

        $this->assertNull($this->service->createAutomaticNotification('brand.created'));
    }

    public function test_nothing_is_created_when_nobody_should_receive_it(): void
    {
        $this->assertNull($this->service->createAutomaticNotification('brand.created'));
        $this->assertSame(0, NotificationReceiver::count());
    }

    public function test_receivers_are_created_in_chunks_for_big_lists(): void
    {
        config(['notification.chunk_size' => 3]);
        $this->receiverUsers(10);

        $message = $this->service->createAutomaticNotification('brand.created', ['brand_name' => 'Big']);

        $this->assertSame(10, $message->receivers()->count());
    }

    public function test_a_manual_notification_uses_the_same_tables(): void
    {
        $users = User::factory()->count(2)->create();
        $creator = User::factory()->create();

        $message = $this->service->createManualNotification(
            'Maintenance',
            'The system will be down tonight.',
            $users->pluck('id')->all(),
            [$this->push->id],
            $creator,
            NotificationPriority::High,
        );

        $this->assertSame(NotificationType::Manual, $message->type);
        $this->assertSame(NotificationPriority::High, $message->priority);
        $this->assertNull($message->notification_setting_id);
        $this->assertSame($creator->id, $message->created_by);
        $this->assertSame(2, $message->receivers()->count());
    }

    public function test_a_manual_notification_with_no_valid_users_or_channels_is_not_created(): void
    {
        $user = User::factory()->create();
        $off = NotificationChannel::factory()->create(['is_active' => false]);

        $this->assertNull($this->service->createManualNotification('T', 'B', [$user->id], [$off->id]));
        $this->assertNull($this->service->createManualNotification('T', 'B', [], [$this->push->id]));
    }
}
