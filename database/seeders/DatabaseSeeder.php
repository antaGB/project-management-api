<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $adminRole = Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']);
        $managerRole = Role::create(['name' => 'manager', 'display_name' => 'Project Manager']);
        $staffRole = Role::create(['name' => 'staff', 'display_name' => 'Regular Staff']);

        $userView = Permission::create(['name' => 'view-user']);
        $userCreate = Permission::create(['name' => 'create-user']);
        $userUpdate = Permission::create(['name' => 'update-user']);
        $userDelete = Permission::create(['name' => 'delete-user']);

        $adminRole->permissions()->attach($userView);
        $adminRole->permissions()->attach($userCreate);
        $adminRole->permissions()->attach($userUpdate);
        $adminRole->permissions()->attach($userDelete);

        // Dummy Users
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('pass123')
        ]);
        $admin->roles()->attach($adminRole);

        $manager = User::create([
            'name' => 'Budi Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('pass123')
        ]);
        $manager->roles()->attach($managerRole);

        $staff = User::create([
            'name' => 'Siti Staff',
            'email' => 'staff@test.com',
            'password' => Hash::make('pass123')
        ]);
        $staff->roles()->attach($staffRole);
    }
}
