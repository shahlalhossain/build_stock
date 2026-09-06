<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeys();

        // Create Roles
        $superAdmin             = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Super Admin', 'description' => 'Super Administrator Role']);

        $permissionManager      = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Permission Manager',   'description' => 'Manage Permissions']);
        $roleManager            = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Role Manager',         'description' => 'Manage Roles']);
        $userManager            = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'User Manager',         'description' => 'Manage Users']);

        $divisionManager        = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Division Manager',          'description' => 'Manage Divisions']);
        $districtManager        = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'District Manager',          'description' => 'Manage Districts']);
        $upazilaManager         = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Upazila Manager',           'description' => 'Manage Upazilas']);

        $loginHistoryManager    = Role::create(['type' => User::TYPE_ADMIN, 'guard_name' => 'web', 'name' => 'Login History Manager',     'description' => 'Manage Login History']);

        $this->enableForeignKeys();
    }
}
