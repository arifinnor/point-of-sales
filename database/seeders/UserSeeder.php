<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $adminUser->assignRole('admin');

        // Create supervisor user
        $supervisorUser = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
        ]);
        $supervisorUser->assignRole('supervisor');

        // Create cashier user
        $cashierUser = User::factory()->create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
        ]);
        $cashierUser->assignRole('cashier');
    }
}
