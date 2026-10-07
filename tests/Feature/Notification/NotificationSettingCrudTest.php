<?php

namespace Tests\Feature\Notification;

use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationSettingService;
use Database\Seeders\NotificationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NotificationPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo([
            'notification-setting.index',
            'notification-setting.create',
            'notification-setting.edit',
            'notification-setting.update-status',
            'notification-setting.destroy',
            'notification-setting.restore',
            'notification-setting.delete',
            'notification-setting.show',
            'notification-setting.trash',
        ]);
        $this->actingAs($this->admin);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Brand Created',
            'event_code' => 'brand.created',
            'description' => 'Sent when a brand is created',
        ], $overrides);
    }

    public function test_index_loads_for_permitted_user(): void
    {
        $this->get(route('notification-setting.index'))->assertOk();
    }

    public function test_create_edit_show_and_trash_pages_render(): void
    {
        $channel = NotificationChannel::factory()->create();
        $setting = app(NotificationSettingService::class)->storeSetting($this->validPayload([
            'channels' => [$channel->id],
            'receiver_users' => [$this->admin->id],
            'receiver_roles' => ['Notification Manager'],
        ]));

        $this->get(route('notification-setting.create'))->assertOk();
        $this->get(route('notification-setting.edit', $setting->id))->assertOk()->assertSee($channel->name);
        $this->get(route('notification-setting.show', $setting->id))->assertOk()->assertSee('brand.created');
        $this->get(route('notification-setting.trash'))->assertOk();
        $this->getJson(route('notification-setting.index'), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
    }

    public function test_index_is_forbidden_without_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('notification-setting.index'))->assertForbidden();
    }

    public function test_store_creates_setting_with_channels_and_receiver_rules(): void
    {
        $channels = NotificationChannel::factory()->count(2)->create();
        $role = Role::firstOrCreate(['name' => 'Notification Manager', 'guard_name' => 'web'], ['type' => 'admin']);
        $receiver = User::factory()->create();

        $response = $this->post(route('notification-setting.store'), $this->validPayload([
            'channels' => $channels->pluck('id')->all(),
            'receiver_users' => [$receiver->id],
            'receiver_roles' => [$role->name],
            'receiver_permissions' => ['notification-setting.index'],
        ]));

        $response->assertRedirect(route('notification-setting.index'));

        $setting = NotificationSetting::where('event_code', 'brand.created')->firstOrFail();
        $this->assertTrue($setting->is_active);
        $this->assertSame($this->admin->id, $setting->created_by);
        $this->assertCount(2, $setting->channels);
        $this->assertSame(3, $setting->receiverRules()->count());
        $this->assertDatabaseHas('notification_setting_receivers', [
            'notification_setting_id' => $setting->id,
            'receiver_type' => 'user',
            'receiver_value' => (string) $receiver->id,
        ]);
    }

    public function test_event_code_must_match_format(): void
    {
        $this->post(route('notification-setting.store'), $this->validPayload(['event_code' => 'BrandCreated']))
            ->assertSessionHasErrors('event_code');

        $this->assertDatabaseCount('notification_settings', 0);
    }

    public function test_event_code_must_be_unique_but_trashed_rows_do_not_block(): void
    {
        $existing = NotificationSetting::factory()->create(['event_code' => 'brand.created']);

        $this->post(route('notification-setting.store'), $this->validPayload())
            ->assertSessionHasErrors('event_code');

        $existing->delete();

        $this->post(route('notification-setting.store'), $this->validPayload())
            ->assertSessionHasNoErrors();
        $this->assertSame(1, NotificationSetting::where('event_code', 'brand.created')->count());
    }

    public function test_update_replaces_channels_and_receiver_rules(): void
    {
        [$first, $second] = NotificationChannel::factory()->count(2)->create();
        $setting = app(NotificationSettingService::class)->storeSetting($this->validPayload([
            'channels' => [$first->id],
            'receiver_roles' => ['Notification Manager'],
        ]));

        $this->patch(route('notification-setting.update', $setting->id), $this->validPayload([
            'name' => 'Brand Created v2',
            'channels' => [$second->id],
            'receiver_permissions' => ['notification-setting.show'],
        ]))->assertRedirect(route('notification-setting.index'));

        $setting->refresh();
        $this->assertSame('Brand Created v2', $setting->name);
        $this->assertSame([$second->id], $setting->channels()->pluck('notification_channels.id')->all());
        $this->assertSame(0, $setting->receiverRules()->where('receiver_type', 'role')->count());
        $this->assertSame(1, $setting->receiverRules()->where('receiver_type', 'permission')->count());
    }

    public function test_toggle_flips_status(): void
    {
        $setting = NotificationSetting::factory()->create(['is_active' => true]);

        $this->post(route('notification-setting.toggle-status', $setting->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => false]);

        $this->assertFalse($setting->fresh()->is_active);
    }

    public function test_restore_is_blocked_when_event_code_is_taken(): void
    {
        $trashed = NotificationSetting::factory()->create(['event_code' => 'brand.created']);
        $trashed->delete();
        NotificationSetting::factory()->create(['event_code' => 'brand.created']);

        $this->post(route('notification-setting.restore', $trashed->id))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSoftDeleted('notification_settings', ['id' => $trashed->id]);
    }

    public function test_destroy_then_restore_works(): void
    {
        $setting = NotificationSetting::factory()->create();

        $this->delete(route('notification-setting.destroy', $setting->id))->assertOk();
        $this->assertSoftDeleted('notification_settings', ['id' => $setting->id]);
        $this->assertSame($this->admin->id, NotificationSetting::withTrashed()->find($setting->id)->deleted_by);

        $this->post(route('notification-setting.restore', $setting->id))->assertOk();
        $this->assertNull(NotificationSetting::find($setting->id)->deleted_at);
    }

    public function test_force_delete_is_blocked_when_messages_exist(): void
    {
        $setting = NotificationSetting::factory()->create();
        NotificationMessage::factory()->create(['notification_setting_id' => $setting->id]);
        $setting->delete();

        $this->delete(route('notification-setting.delete', $setting->id))
            ->assertStatus(422)
            ->assertJson(['message' => 'This setting has notification history and cannot be deleted permanently.']);

        $this->assertDatabaseHas('notification_settings', ['id' => $setting->id]);
    }

    public function test_force_delete_removes_setting_without_messages(): void
    {
        $setting = NotificationSetting::factory()->create();
        $setting->delete();

        $this->delete(route('notification-setting.delete', $setting->id))->assertOk();

        $this->assertDatabaseMissing('notification_settings', ['id' => $setting->id]);
    }

    public function test_service_throws_general_exception_on_blocked_restore(): void
    {
        $trashed = NotificationSetting::factory()->create(['event_code' => 'a.b']);
        $trashed->delete();
        NotificationSetting::factory()->create(['event_code' => 'a.b']);

        $this->expectException(GeneralException::class);
        app(NotificationSettingService::class)->restoreSetting($trashed->id);
    }
}
