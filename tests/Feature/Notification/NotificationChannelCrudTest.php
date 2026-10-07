<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationChannel;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationChannelCrudTest extends TestCase
{
    use RefreshDatabase;

    protected const PERMISSIONS = [
        'index', 'show', 'create', 'edit', 'update-status', 'destroy', 'trash', 'restore', 'delete',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Creates a user who holds every notification-channel permission.
     */
    protected function userWithAllPermissions(): User
    {
        $user = User::factory()->create();

        foreach (self::PERMISSIONS as $action) {
            $permission = Permission::firstOrCreate(
                ['name' => "notification-channel.{$action}", 'guard_name' => 'web', 'type' => 'admin']
            );
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'SMS Gateway',
            'code' => 'sms_gateway',
            'driver' => 'sms',
            'description' => 'Sends text messages',
            'sort_order' => 3,
        ], $overrides);
    }

    public function test_list_page_loads_for_permitted_user(): void
    {
        $this->actingAs($this->userWithAllPermissions())
            ->get(route('notification-channel.index'))
            ->assertOk();
    }

    public function test_create_edit_show_and_trash_pages_render(): void
    {
        $channel = NotificationChannel::factory()->create();
        $this->actingAs($this->userWithAllPermissions());

        $this->get(route('notification-channel.create'))->assertOk();
        $this->get(route('notification-channel.edit', $channel->id))->assertOk()->assertSee('disabled', false);
        $this->get(route('notification-channel.show', $channel->id))->assertOk()->assertSee($channel->code);
        $this->get(route('notification-channel.trash'))->assertOk();
    }

    public function test_user_without_permission_gets_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('notification-channel.index'))
            ->assertForbidden();
    }

    public function test_store_rejects_duplicate_code_including_trashed_rows(): void
    {
        $trashed = NotificationChannel::factory()->create(['code' => 'sms_gateway']);
        $trashed->delete();

        $this->actingAs($this->userWithAllPermissions())
            ->post(route('notification-channel.store'), $this->validPayload())
            ->assertSessionHasErrors('code');
    }

    public function test_store_rejects_bad_code_format(): void
    {
        $this->actingAs($this->userWithAllPermissions())
            ->post(route('notification-channel.store'), $this->validPayload(['code' => 'Bad Code!']))
            ->assertSessionHasErrors('code');
    }

    public function test_store_creates_an_active_channel(): void
    {
        $user = $this->userWithAllPermissions();

        $this->actingAs($user)
            ->post(route('notification-channel.store'), $this->validPayload())
            ->assertRedirect(route('notification-channel.index'));

        $channel = NotificationChannel::where('code', 'sms_gateway')->firstOrFail();
        $this->assertTrue($channel->is_active);
        $this->assertSame($user->id, $channel->created_by);
        $this->assertSame(3, $channel->sort_order);
    }

    public function test_update_does_not_change_the_code(): void
    {
        $channel = NotificationChannel::factory()->create(['code' => 'push']);

        $this->actingAs($this->userWithAllPermissions())
            ->patch(route('notification-channel.update', $channel->id), $this->validPayload(['code' => 'hacked']))
            ->assertRedirect(route('notification-channel.index'));

        $channel->refresh();
        $this->assertSame('push', $channel->code);
        $this->assertSame('SMS Gateway', $channel->name);
    }

    public function test_toggle_flips_the_status(): void
    {
        $channel = NotificationChannel::factory()->create(['is_active' => true]);
        $this->actingAs($this->userWithAllPermissions());

        $this->postJson(route('notification-channel.toggle-status', $channel->id))
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertFalse($channel->fresh()->is_active);

        $this->postJson(route('notification-channel.toggle-status', $channel->id))->assertOk();
        $this->assertTrue($channel->fresh()->is_active);
    }

    public function test_destroy_is_blocked_when_linked_to_a_setting(): void
    {
        $channel = NotificationChannel::factory()->create();
        $setting = NotificationSetting::factory()->create();
        $channel->settings()->attach($setting->id, ['is_active' => true]);

        $this->actingAs($this->userWithAllPermissions())
            ->deleteJson(route('notification-channel.destroy', $channel->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertNotSoftDeleted($channel);
    }

    public function test_destroy_moves_unlinked_channel_to_trash_and_restore_brings_it_back(): void
    {
        $user = $this->userWithAllPermissions();
        $channel = NotificationChannel::factory()->create();
        $this->actingAs($user);

        $this->deleteJson(route('notification-channel.destroy', $channel->id))->assertOk();
        $this->assertSoftDeleted($channel);
        $this->assertSame($user->id, $channel->fresh()->withTrashed()->find($channel->id)->deleted_by);

        $this->postJson(route('notification-channel.restore', $channel->id))->assertOk();
        $restored = NotificationChannel::find($channel->id);
        $this->assertNotNull($restored);
        $this->assertTrue($restored->is_active);
        $this->assertNull($restored->deleted_by);
    }

    public function test_force_delete_is_blocked_when_receivers_exist(): void
    {
        $channel = NotificationChannel::factory()->create();
        NotificationReceiver::factory()->create(['channel_id' => $channel->id]);
        $channel->delete();

        $this->actingAs($this->userWithAllPermissions())
            ->deleteJson(route('notification-channel.delete', $channel->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('notification_channels', ['id' => $channel->id]);
    }

    public function test_force_delete_works_without_receivers(): void
    {
        $channel = NotificationChannel::factory()->create();
        $channel->delete();

        $this->actingAs($this->userWithAllPermissions())
            ->deleteJson(route('notification-channel.delete', $channel->id))
            ->assertOk();

        $this->assertDatabaseMissing('notification_channels', ['id' => $channel->id]);
    }
}
