<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionAssignSeeder extends Seeder
{

    use DisableForeignKeys;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Role
        $permissionManager      = Role::findByName('Permission Manager');
        $roleManager            = Role::findByName('Role Manager');
        $userManager            = Role::findByName('User Manager');

        $divisionManager        = Role::findByName('Division Manager');
        $districtManager        = Role::findByName('District Manager');
        $upazilaManager         = Role::findByName('Upazila Manager');

        $loginHistoryManager    = Role::findByName('Login History Manager');

        // Assign Permissions to Role
        $permissionManager->givePermissionTo(['manage.permission', 'permission.index', 'permission.list.download', 'permission.show', 'permission.edit', 'permission.create', 'permission.destroy', 'permission.trash', 'permission.restore', 'permission.delete']);
        $roleManager->givePermissionTo(['manage.role', 'role.index', 'role.list.download', 'role.show', 'role.edit', 'role.create', 'role.destroy', 'role.trash', 'role.restore', 'role.delete']);
        $userManager->givePermissionTo(['manage.users', 'users.index', 'users.search', 'users.list.download', 'users.show', 'users.details.download', 'users.edit', 'users.create', 'users.destroy', 'users.trash', 'users.restore', 'users.delete', 'users.change-password']);

        $districtManager->givePermissionTo(['manage.district', 'district.index', 'district.list.download', 'district.show', 'district.edit', 'district.create', 'district.destroy', 'district.trash', 'district.restore', 'district.delete']);
        $divisionManager->givePermissionTo(['manage.division', 'division.index', 'division.list.download', 'division.show', 'division.edit', 'division.create', 'division.destroy', 'division.trash', 'division.restore', 'division.delete']);
        $upazilaManager->givePermissionTo(['manage.upazila', 'upazila.index', 'upazila.list.download', 'upazila.show', 'upazila.edit', 'upazila.create', 'upazila.destroy', 'upazila.trash', 'upazila.restore', 'upazila.delete']);

        $loginHistoryManager->givePermissionTo(['manage.login-history', 'login-history.index', 'login-history.list.download', 'login-history.show', 'login-history.delete']);

        $this->enableForeignKeys();
    }
}
