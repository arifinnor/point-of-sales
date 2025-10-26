<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in order: Tenants first, then roles/permissions, then users
        $this->call([
            TenantSeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('Super Admin: superadmin@example.com');
        $this->command->info('Admin: admin@example.com');
        $this->command->info('Supervisor: supervisor@example.com');
        $this->command->info('Cashier: cashier@example.com');
        $this->command->info('Default password for all users: password');
    }
}
