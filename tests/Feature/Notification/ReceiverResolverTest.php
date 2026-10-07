<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Notification\ReceiverResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiverResolverTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting = NotificationSetting::factory()->create();
    }

    protected function addRule(string $type, string|int $value): void
    {
        $this->setting->receiverRules()->create(['receiver_type' => $type, 'receiver_value' => (string) $value]);
    }

    protected function resolvedIds(): array
    {
        return (new ReceiverResolver)->resolve($this->setting)->pluck('id')->sort()->values()->all();
    }

    public function test_specific_users_are_resolved(): void
    {
        $users = User::factory()->count(2)->create();
        User::factory()->create();
        $this->addRule(NotificationSettingReceiver::TYPE_USER, $users[0]->id);
        $this->addRule(NotificationSettingReceiver::TYPE_USER, $users[1]->id);

        $this->assertSame($users->pluck('id')->sort()->values()->all(), $this->resolvedIds());
    }

    public function test_users_with_a_role_are_resolved(): void
    {
        $role = Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Brand Viewer']);
        $withRole = User::factory()->create()->assignRole($role);
        User::factory()->create();
        $this->addRule(NotificationSettingReceiver::TYPE_ROLE, 'Brand Viewer');

        $this->assertSame([$withRole->id], $this->resolvedIds());
    }

    public function test_users_with_a_permission_are_resolved_and_others_are_excluded(): void
    {
        $permission = Permission::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'brand.index']);
        $role = Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Viewer'])->givePermissionTo($permission);

        $viaRole = User::factory()->create()->assignRole($role);
        $direct = User::factory()->create()->givePermissionTo($permission);
        User::factory()->create(); // has no permission
        $this->addRule(NotificationSettingReceiver::TYPE_PERMISSION, 'brand.index');

        $this->assertSame(collect([$viaRole->id, $direct->id])->sort()->values()->all(), $this->resolvedIds());
    }

    public function test_inactive_users_and_duplicates_are_left_out(): void
    {
        $active = User::factory()->create();
        $inactive = User::factory()->create(['is_active' => false]);
        $role = Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Dup'])->givePermissionTo(
            Permission::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'x.y'])
        );
        $active->assignRole($role);
        $this->addRule(NotificationSettingReceiver::TYPE_USER, $active->id);
        $this->addRule(NotificationSettingReceiver::TYPE_USER, $inactive->id);
        $this->addRule(NotificationSettingReceiver::TYPE_ROLE, 'Dup');

        $this->assertSame([$active->id], $this->resolvedIds());
    }

    public function test_roles_and_permissions_that_no_longer_exist_are_ignored(): void
    {
        $this->addRule(NotificationSettingReceiver::TYPE_ROLE, 'Ghost Role');
        $this->addRule(NotificationSettingReceiver::TYPE_PERMISSION, 'ghost.permission');

        $this->assertSame([], $this->resolvedIds());
    }
}
