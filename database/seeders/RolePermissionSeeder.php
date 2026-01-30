<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() 
    {
        // Roles
        $adminRole = Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']);
        $managerRole = Role::create(['name' => 'manager', 'display_name' => 'Project Manager']);
        $staffRole = Role::create(['name' => 'staff', 'display_name' => 'Regular Staff']);

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
