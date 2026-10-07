<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Database\Seeders\NotificationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ManualNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;

    protected NotificationChannel $push;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NotificationPermissionSeeder::class);

        $this->push = NotificationChannel::factory()->create(['code' => 'push', 'name' => 'Push', 'driver' => 'push']);
        $this->sender = User::factory()->create(['type' => 'admin'])->givePermissionTo(['notification.send', 'notification.show']);
    }

    protected function payload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Maintenance',
            'message' => 'The system will be down tonight.',
            'priority' => 'high',
            'channels' => [$this->push->id],
            'users' => [User::factory()->create()->id],
        ];
    }

    // ---------- access ----------

    public function test_users_without_the_send_permission_cannot_use_any_manual_route(): void
    {
        $nobody = User::factory()->create();

        $this->actingAs($nobody)->get(route('notification.create'))->assertForbidden();
        $this->actingAs($nobody)->getJson(route('notification.receiver-search'))->assertForbidden();
        $this->actingAs($nobody)->postJson(route('notification.preview'), $this->payload())->assertForbidden();
        $this->actingAs($nobody)->post(route('notification.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('notification.create'))->assertRedirect(route('login'));
    }

    public function test_the_compose_page_lists_active_channels_and_roles(): void
    {
        NotificationChannel::factory()->create(['name' => 'Hidden Channel', 'is_active' => false]);
        Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Store Manager X']);

        $this->actingAs($this->sender)->get(route('notification.create'))
            ->assertOk()
            ->assertSee('Push')
            ->assertSee('Store Manager X')
            ->assertDontSee('Hidden Channel');
    }

    // ---------- receiver search ----------

    public function test_receiver_search_finds_only_active_users_and_is_limited(): void
    {
        User::factory()->create(['name' => 'Alice Active']);
        User::factory()->create(['name' => 'Alice Inactive', 'is_active' => false]);
        User::factory()->count(30)->create(['name' => 'Bulk Person']);

        $alice = $this->actingAs($this->sender)->getJson(route('notification.receiver-search', ['q' => 'Alice']))->assertOk()->json('results');
        $bulk = $this->actingAs($this->sender)->getJson(route('notification.receiver-search', ['q' => 'Bulk']))->json('results');

        $this->assertCount(1, $alice);
        $this->assertStringContainsString('Alice Active', $alice[0]['text']);
        $this->assertCount(20, $bulk);
    }

    // ---------- preview ----------

    public function test_the_preview_counts_people_and_deliveries_and_saves_nothing(): void
    {
        Queue::fake();
        $role = Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Viewer']);
        $viaRole = User::factory()->create()->assignRole($role);
        $direct = User::factory()->create();
        $optedOut = User::factory()->create();
        UserNotificationPreference::create(['user_id' => $optedOut->id, 'channel_id' => $this->push->id, 'is_enabled' => false]);

        $response = $this->actingAs($this->sender)->postJson(route('notification.preview'), $this->payload([
            'users' => [$direct->id, $optedOut->id, $viaRole->id],
            'roles' => ['Viewer'],
        ]))->assertOk()->json('preview');

        $this->assertSame(3, $response['receiver_count']);
        $this->assertSame(2, $response['delivery_count']);
        $this->assertSame(1, $response['skipped_by_preference']);
        $this->assertSame(['Push'], $response['channels']);
        $this->assertSame('High', $response['priority']);
        $this->assertSame(0, NotificationMessage::count());
        Queue::assertNothingPushed();
    }

    public function test_the_preview_checks_the_form_like_send_does(): void
    {
        $this->actingAs($this->sender)->postJson(route('notification.preview'), ['title' => 'x'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message', 'priority', 'channels', 'users']);
    }

    // ---------- sending ----------

    public function test_sending_creates_a_manual_notification_with_receivers_and_queues_the_jobs(): void
    {
        Queue::fake();
        $recipients = User::factory()->count(2)->create();

        $response = $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => $recipients->pluck('id')->all()]));

        $message = NotificationMessage::sole();
        $response->assertRedirect(route('notification.show', $message->id));
        $this->assertSame(NotificationType::Manual, $message->type);
        $this->assertSame(NotificationPriority::High, $message->priority);
        $this->assertSame('Maintenance', $message->title);
        $this->assertSame($this->sender->id, $message->created_by);
        $this->assertNull($message->notification_setting_id);
        $this->assertEqualsCanonicalizing($recipients->pluck('id')->all(), $message->receivers->pluck('user_id')->all());
        $this->assertSame(NotificationReceiverStatus::Queued, $message->receivers->first()->status);
        Queue::assertPushed(SendNotificationJob::class, 2);
    }

    public function test_a_scheduled_notification_is_delayed(): void
    {
        Queue::fake();

        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['scheduled_at' => now()->addDay()->format('Y-m-d\TH:i')]))
            ->assertRedirect();

        $this->assertNotNull(NotificationMessage::sole()->scheduled_at);
        Queue::assertPushed(SendNotificationJob::class, fn ($job) => $job->delay !== null);
    }

    public function test_invalid_data_is_rejected_and_nothing_is_created(): void
    {
        $inactive = User::factory()->create(['is_active' => false]);
        $off = NotificationChannel::factory()->create(['is_active' => false]);

        $this->actingAs($this->sender)->from(route('notification.create'))->post(route('notification.store'), $this->payload([
            'channels' => [$off->id],
            'users' => [$inactive->id],
            'priority' => 'whenever',
            'scheduled_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))->assertRedirect(route('notification.create'))
            ->assertSessionHasErrors(['channels.0', 'users.0', 'priority', 'scheduled_at']);

        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_at_least_one_user_or_role_is_required(): void
    {
        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => []]))
            ->assertSessionHasErrors('users');
    }

    public function test_nothing_is_created_when_every_receiver_switched_the_channel_off(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        UserNotificationPreference::create(['user_id' => $user->id, 'channel_id' => $this->push->id, 'is_enabled' => false]);

        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => [$user->id]]))
            ->assertSessionHas('error');

        $this->assertSame(0, NotificationMessage::count());
        Queue::assertNothingPushed();
    }

    public function test_sending_to_too_many_people_is_refused(): void
    {
        config(['notification.manual.max_receivers' => 2]);
        $users = User::factory()->count(3)->create();

        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => $users->pluck('id')->all()]))
            ->assertSessionHas('error');

        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_a_manual_notification_is_delivered_by_push_through_the_same_queue_pipeline(): void
    {
        $recipient = User::factory()->create();
        $recipient->deviceTokens()->create(['token' => 'manual-device-token-123456']);

        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => [$recipient->id]]));

        $receiver = NotificationMessage::sole()->receivers->sole();
        $this->assertSame(NotificationReceiverStatus::Sent, $receiver->status);
        $this->assertSame(NotificationStatus::Completed, $receiver->message->status);
    }

    // ---------- details page ----------

    public function test_the_details_page_needs_the_show_permission_and_lists_deliveries_and_history(): void
    {
        $recipient = User::factory()->create(['name' => 'Rita Receiver']);
        $this->actingAs($this->sender)->post(route('notification.store'), $this->payload(['users' => [$recipient->id]]));
        $message = NotificationMessage::sole();

        $this->actingAs($this->sender)->get(route('notification.show', $message->id))
            ->assertOk()
            ->assertSee('Maintenance')
            ->assertSee('Rita Receiver')
            ->assertSee('failed');

        $onlySend = User::factory()->create()->givePermissionTo('notification.send');
        $this->actingAs($onlySend)->get(route('notification.show', $message->id))->assertForbidden();
    }
}
