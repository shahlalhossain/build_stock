<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Non-Grouped/Uncategorized Permissions
        $superAdminPermission = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'All Permissions', 'description' => 'All Permissions']);

        // Grouped/Categorized Permissions
        // Manage Permissions
        $managePermission = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.permission', 'description' => 'Manage Permissions']);
        $managePermission->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.index',              'description' => 'View Permission List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.list.download',      'description' => 'Permission List Download']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.show',               'description' => 'View Permission Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.edit',               'description' => 'Update Permission']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.create',             'description' => 'Create New Permission']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.destroy',            'description' => 'Soft Delete Permission']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.trash',              'description' => 'Soft Deleted Permission List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.restore',            'description' => 'Restore Permission from Trash']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'permission.delete',             'description' => 'Delete Permission Permanently'])
        ]);

        // Manage Roles
        $mangeRole = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.role', 'description' => 'Manage Roles']);
        $mangeRole->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.index',              'description' => 'View Role List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.list.download',      'description' => 'Role List Download']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.show',               'description' => 'View Role Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.edit',               'description' => 'Update Role Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.create',             'description' => 'Create New Role']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.destroy',            'description' => 'Soft Delete Role']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.trash',              'description' => 'Soft Deleted Role List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.restore',            'description' => 'Restore Role from Trash']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'role.delete',             'description' => 'Delete Role Permanently'])
        ]);

        // Manage Users
        $mangeUser = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.users', 'description' => 'Manage Users']);
        $mangeUser->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.index',              'description' => 'View User List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.search',             'description' => 'Search & Filter User']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.list.download',      'description' => 'User List Download']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.show',               'description' => 'View User Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.details.download',   'description' => 'User Details Download']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.edit',               'description' => 'Edit & Update User Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.create',             'description' => 'Create New User']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.destroy',            'description' => 'Soft Delete User']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.trash',              'description' => 'Soft Deleted User List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.restore',            'description' => 'Restore User from Trash']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.delete',             'description' => 'Delete User Permanently']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'users.change-password',    'description' => 'Change Password'])
        ]);

        // Manage Divisions
        $manageDivision = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.division', 'description' => 'Manage Division']);
        $manageDivision->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.index',         'description' => 'View Division List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.list.download', 'description' => 'Download Division List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.show',          'description' => 'View Division Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.edit',          'description' => 'Edit Division']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.create',        'description' => 'Create Division']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.destroy',       'description' => 'Soft Delete Division']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.trash',         'description' => 'Trashed Division List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.restore',       'description' => 'Restore Division']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'division.delete',        'description' => 'Delete Division Permanently']),
        ]);

        // Manage Districts
        $manageDistrict = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.district', 'description' => 'Manage District']);
        $manageDistrict->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.index',         'description' => 'View District List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.list.download', 'description' => 'Download District List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.show',          'description' => 'View District Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.edit',          'description' => 'Edit District']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.create',        'description' => 'Create District']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.destroy',       'description' => 'Soft Delete District']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.trash',         'description' => 'Trashed District List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.restore',       'description' => 'Restore District']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'district.delete',        'description' => 'Delete District Permanently']),
        ]);

        // Manage Upazilas & Thanas
        $manageUpazila = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.upazila', 'description' => 'Manage Upazila']);
        $manageUpazila->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.index',         'description' => 'View Upazila List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.list.download', 'description' => 'Download Upazila List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.show',          'description' => 'View Upazila Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.edit',          'description' => 'Edit Upazila']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.create',        'description' => 'Create Upazila']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.destroy',       'description' => 'Soft Delete Upazila']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.trash',         'description' => 'Trashed Upazila List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.restore',       'description' => 'Restore Upazila']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'upazila.delete',        'description' => 'Delete Upazila Permanently']),
        ]);

        // Manage Login History
        $manageLoginHistory = Permission::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'manage.login-history', 'description' => 'Manage Login History']);
        $manageLoginHistory->children()->saveMany([
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'login-history.index',         'description' => 'View Login History List']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'login-history.list.download', 'description' => 'Download Login History']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'login-history.show',          'description' => 'View Login History Details']),
            new Permission(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'login-history.delete',        'description' => 'Delete Login History Permanently']),
        ]);
    }
}
