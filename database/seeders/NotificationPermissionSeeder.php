<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the permissions that protect the Notification screens, and a
 * "Notification Manager" role that holds all of them.
 *
 * Not part of AuthSeeder on purpose (AuthSeeder wipes users, roles and permissions).
 * Safe to run many times.
 * Run with: php artisan db:seed --class=NotificationPermissionSeeder
 */
class NotificationPermissionSeeder extends Seeder
{
    /**
     * Each screen (module) and the actions people can do on it.
     * Format: 'permission-prefix' => ['Screen Name', ['action' => 'Description with %s for the screen name']]
     */
    protected const MODULES = [
        'notification-channel' => ['Notification Channel', [
            'index' => 'View %s List',
            'show' => 'View %s Details',
            'create' => 'Create New %s',
            'edit' => 'Edit & Update %s',
            'update-status' => 'Enable/Disable %s',
            'destroy' => 'Soft Delete %s',
            'trash' => 'Soft Deleted %s List',
            'restore' => 'Restore %s from Trash',
            'delete' => 'Delete %s Permanently',
        ]],
        'notification-setting' => ['Notification Setting', [
            'index' => 'View %s List',
            'show' => 'View %s Details',
            'create' => 'Create New %s',
            'edit' => 'Edit & Update %s',
            'update-status' => 'Enable/Disable %s',
            'destroy' => 'Soft Delete %s',
            'trash' => 'Soft Deleted %s List',
            'restore' => 'Restore %s from Trash',
            'delete' => 'Delete %s Permanently',
        ]],
        'notification-template' => ['Notification Template', [
            'index' => 'View %s List',
            'show' => 'View %s Details',
            'create' => 'Create New %s',
            'edit' => 'Edit & Update %s',
            'update-status' => 'Enable/Disable %s',
            'delete' => 'Delete %s',
        ]],
        'notification' => ['Notification', [
            'index' => 'View %s List',
            'show' => 'View %s Details',
            'send' => 'Send Manual %s',
        ]],
        'notification-log' => ['Notification Log', [
            'index' => 'View %s List',
            'show' => 'View %s Details',
        ]],
    ];

    /**
     * Creates every permission and gives them all to the "Notification Manager" role.
     */
    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissionNames = [];

        foreach (self::MODULES as $prefix => [$label, $actions]) {
            $allPermissionNames = array_merge(
                $allPermissionNames,
                $this->createModulePermissions($prefix, $label, $actions)
            );
        }

        $role = Role::firstOrCreate(
            ['name' => 'Notification Manager', 'guard_name' => 'web'],
            ['type' => User::TYPE_ADMIN, 'description' => 'Manage Notifications']
        );
        $role->givePermissionTo($allPermissionNames);

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Creates one "manage.xxx" parent permission and its action permissions
     * for a single screen. Returns the names of all permissions created.
     */
    protected function createModulePermissions(string $prefix, string $label, array $actions): array
    {
        $manageName = "manage.{$prefix}";

        $manage = Permission::firstOrCreate(
            ['name' => $manageName, 'guard_name' => 'web'],
            ['type' => User::TYPE_ADMIN, 'description' => "Manage {$label}"]
        );

        $names = [$manageName];

        foreach ($actions as $action => $descriptionTemplate) {
            $permission = Permission::firstOrCreate(
                ['name' => "{$prefix}.{$action}", 'guard_name' => 'web'],
                [
                    'type' => User::TYPE_ADMIN,
                    'parent_id' => $manage->id,
                    'description' => sprintf($descriptionTemplate, $label),
                ]
            );

            $names[] = $permission->name;
        }

        return $names;
    }
}
