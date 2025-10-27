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

        if ($tenants->isEmpty()) {
            $this->command->error('No tenants found. Please run TenantSeeder first.');

            return;
        }

        $firstTenant = $tenants->first();

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

        $this->command->info("Created Super Admin with access to all {$tenants->count()} tenants.");

        // Create standard users (admin, supervisor, cashier) for each tenant
        foreach ($tenants as $index => $tenant) {
            setPermissionsTeamId($tenant->id);

            $tenantNumber = $index + 1;
            $tenantSlug = $this->getTenantSlug($tenant->name, $tenantNumber);

            $this->command->info("Creating users for tenant: {$tenant->name}");

            // Create admin for this tenant
            $admin = User::factory()->create([
                'name' => "Admin {$tenant->name}",
                'email' => "admin.{$tenantSlug}@example.com",
            ]);
            $admin->assignRole('admin');
            $admin->tenants()->attach($tenant->id, ['is_default' => true]);
            $this->command->info("  ✓ Created admin: {$admin->email}");

            // Create supervisor for this tenant
            $supervisor = User::factory()->create([
                'name' => "Supervisor {$tenant->name}",
                'email' => "supervisor.{$tenantSlug}@example.com",
            ]);
            $supervisor->assignRole('supervisor');
            $supervisor->tenants()->attach($tenant->id, ['is_default' => true]);
            $this->command->info("  ✓ Created supervisor: {$supervisor->email}");

            // Create cashier for this tenant
            $cashier = User::factory()->create([
                'name' => "Cashier {$tenant->name}",
                'email' => "cashier.{$tenantSlug}@example.com",
            ]);
            $cashier->assignRole('cashier');
            $cashier->tenants()->attach($tenant->id, ['is_default' => true]);
            $this->command->info("  ✓ Created cashier: {$cashier->email}");
        }

        $this->command->info('✅ All users created successfully!');
        $this->command->newLine();
        $this->command->info('Login credentials (password: password):');
        $this->command->info('  Super Admin: superadmin@example.com');

        foreach ($tenants as $index => $tenant) {
            $tenantNumber = $index + 1;
            $tenantSlug = $this->getTenantSlug($tenant->name, $tenantNumber);
            $this->command->info("  {$tenant->name}:");
            $this->command->info("    - admin.{$tenantSlug}@example.com");
            $this->command->info("    - supervisor.{$tenantSlug}@example.com");
            $this->command->info("    - cashier.{$tenantSlug}@example.com");
        }
    }

    /**
     * Generate a slug from tenant name for email addresses.
     */
    private function getTenantSlug(string $tenantName, int $index): string
    {
        // Convert tenant name to a simple slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $tenantName));

        // If slug is empty or too generic, use tenant number
        if (empty($slug) || strlen($slug) < 3) {
            return "tenant{$index}";
        }

        return $slug;
    }
}
