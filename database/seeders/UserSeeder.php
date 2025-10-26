<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();
        $firstTenant = $tenants->first();

        if (! $firstTenant) {
            $this->command->error('No tenants found. Please run TenantSeeder first.');

            return;
        }

        // Set tenant context for Spatie Permission
        setPermissionsTeamId($firstTenant->id);

        // Create super admin user (access to all tenants)
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        $superAdmin->assignRole('super-admin');
        // Attach super admin to all tenants
        foreach ($tenants as $tenant) {
            $superAdmin->tenants()->attach($tenant->id, ['is_default' => $tenant->id === $firstTenant->id]);
        }

        // Create admin user for first tenant
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $adminUser->assignRole('admin');
        $adminUser->tenants()->attach($firstTenant->id, ['is_default' => true]);

        // Create supervisor user for first tenant
        $supervisorUser = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
        ]);
        $supervisorUser->assignRole('supervisor');
        $supervisorUser->tenants()->attach($firstTenant->id, ['is_default' => true]);

        // Create cashier user for first tenant
        $cashierUser = User::factory()->create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
        ]);
        $cashierUser->assignRole('cashier');
        $cashierUser->tenants()->attach($firstTenant->id, ['is_default' => true]);

        // Create users for second tenant if exists
        if ($tenants->count() > 1) {
            $secondTenant = $tenants->get(1);
            setPermissionsTeamId($secondTenant->id);

            $tenant2Admin = User::factory()->create([
                'name' => 'Tenant 2 Admin',
                'email' => 'admin.tenant2@example.com',
            ]);
            $tenant2Admin->assignRole('admin');
            $tenant2Admin->tenants()->attach($secondTenant->id, ['is_default' => true]);
        }

        $this->command->info('Created users and attached them to tenants.');
    }
}
