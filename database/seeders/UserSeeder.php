<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users with different roles
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);
        $superAdmin->assignRole('super_admin');

        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);
        $manager->assignRole('manager');

        $cashier = User::factory()->create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
        ]);
        $cashier->assignRole('cashier');

        $staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
        ]);
        $staff->assignRole('staff');
    }
}
