<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Standalone Seeder for the Store-Scoped Inventory Modules (Product Requisition,
 * Purchase, Transfer, Receive, Delivery, Stock Transaction).
 *
 * Deliberately NOT wired into AuthSeeder's chain — AuthSeeder truncates
 * users/roles/permissions on every run, which would wipe this Project's real
 * seeded data (55+ Users, Stores, Requisitions, Purchases, ...). Run this one
 * standalone instead: `php artisan db:seed --class=InventoryPermissionSeeder`.
 * Safe to re-run — every write is guarded by firstOrCreate()/syncPermissions().
 */
class InventoryPermissionSeeder extends Seeder
{
    /**
     * Module Slug => Human Label, matching each Module's Route Prefix.
     */
    protected const MODULES = [
        'product-requisition' => 'Product Requisition',
        'product-purchase' => 'Product Purchase',
        'product-transfer' => 'Product Transfer',
        'product-receive' => 'Product Receive',
        'product-delivery' => 'Product Delivery',
        'stock-transaction' => 'Stock Transaction',
    ];

    /**
     * Action Suffix => [Permission Suffix, Description Verb] — mirrors the
     * exact Shape PermissionSeeder.php already uses for every other Module.
     */
    protected const ACTIONS = [
        'index' => 'View %s List',
        'list.download' => '%s List Download',
        'show' => 'View %s Details',
        'create' => 'Create New %s',
        'edit' => 'Edit & Update %s',
        'update-status' => 'Approve/Reject %s',
        'destroy' => 'Soft Delete %s',
        'trash' => 'Soft Deleted %s List',
        'restore' => 'Restore %s from Trash',
        'delete' => 'Delete %s Permanently',
    ];

    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::MODULES as $slug => $label) {
            $manageName = "manage.{$slug}";

            $manage = Permission::firstOrCreate(
                ['name' => $manageName, 'guard_name' => 'web'],
                ['type' => User::TYPE_ADMIN, 'description' => "Manage {$label}"]
            );

            $childPermissions = [$manageName];

            foreach (self::ACTIONS as $actionSuffix => $descriptionTemplate) {
                $name = "{$slug}.{$actionSuffix}";

                $child = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    [
                        'type' => User::TYPE_ADMIN,
                        'parent_id' => $manage->id,
                        'description' => sprintf($descriptionTemplate, $label),
                    ]
                );

                $childPermissions[] = $child->name;
            }

            $this->assignRolesFor($slug, $label, $childPermissions);
        }

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Store Manager gets every Action (incl. Approve/Reject via update-status).
     * Store Keeper gets everything EXCEPT update-status/destroy/delete — a
     * starting Point Admins can widen later via the Role Management UI; this
     * Project's actual Capability-Gate is "whatever Permissions a Role holds",
     * not a hardcoded Manager-vs-Keeper split (see conversation decision).
     */
    protected function assignRolesFor(string $slug, string $label, array $allPermissionNames): void
    {
        $storeManagerRole = Role::firstOrCreate(
            ['name' => 'Store Manager', 'guard_name' => 'web'],
            ['type' => User::TYPE_ADMIN, 'description' => 'Manage a Store/Warehouse\'s Inventory Operations']
        );

        $storeKeeperRole = Role::firstOrCreate(
            ['name' => 'Store Keeper', 'guard_name' => 'web'],
            ['type' => User::TYPE_ADMIN, 'description' => 'Operate a Store/Warehouse\'s Day-to-Day Inventory']
        );

        $storeManagerRole->givePermissionTo($allPermissionNames);

        $keeperExcluded = ["{$slug}.update-status", "{$slug}.destroy", "{$slug}.delete"];
        $keeperPermissionNames = array_values(array_diff($allPermissionNames, $keeperExcluded));

        $storeKeeperRole->givePermissionTo($keeperPermissionNames);
    }
}
