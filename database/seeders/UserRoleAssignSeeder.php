<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleAssignSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeys();

        //User::find(1)->assignRole(config('boilerplate.access.role.admin'));
        User::find(1)->assignRole(['Super Admin']);
        User::find(2)->assignRole(['Permission Manager', 'Role Manager', 'User Manager']);
        User::find(3)->assignRole(['Role Manager']);
        User::find(4)->assignRole(['User Manager']);

        $this->enableForeignKeys();
    }
}
