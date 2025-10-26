<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Sales permissions
            'create_sale' => 'Create new sales transactions',
            'void_sale' => 'Void sales transactions',
            'view_sale' => 'View sales transactions',

            // Return permissions
            'create_return' => 'Create returns (with amount constraints: '.config('pos.currency.symbol', 'Rp').number_format(config('pos.constraints.cashier.max_return_amount', 1000000)).')',
            'create_unlimited_return' => 'Create returns without amount constraints',

            // Product permissions
            'view_product' => 'View product information',
            'manage_product' => 'Create, update, and delete products',

            // Inventory permissions
            'view_inventory' => 'View inventory levels',
            'adjust_stock' => 'Adjust stock levels (with quantity constraints: ±'.config('pos.constraints.supervisor.max_stock_adjustment', 5).')',
            'unlimited_stock_adjustment' => 'Adjust stock levels without constraints',

            // Shift permissions
            'open_shift' => 'Open cashier shifts',
            'close_shift' => 'Close cashier shifts',
            'view_shift' => 'View shift information',

            // Discount permissions
            'apply_discount' => 'Apply basic discounts',
            'approve_discount' => 'Approve larger discounts',

            // Report permissions
            'view_reports' => 'View daily and other reports',
            'generate_reports' => 'Generate custom reports',

            // User management permissions
            'view_user' => 'View user information',
            'manage_user' => 'Create, update, and delete users',

            // Role management permissions
            'view_role' => 'View role information',
            'manage_role' => 'Assign and manage user roles',

            // Settings permissions
            'view_settings' => 'View system settings',
            'manage_settings' => 'Modify system settings',

            // Outlet permissions
            'view_outlet' => 'View outlet information',
            'manage_outlet' => 'Create and manage outlets',
        ];

        foreach ($permissions as $permission => $description) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Create roles and assign permissions
        $this->createSuperAdminRole();
        $this->createCashierRole();
        $this->createSupervisorRole();
        $this->createAdminRole();
    }

    private function createSuperAdminRole(): void
    {
        // Super Admin role for cross-tenant access
        $superAdmin = Role::updateOrCreate(
            ['name' => 'super-admin'],
            ['guard_name' => 'web']
        );

        // Super Admin has all permissions across all tenants
        $superAdmin->givePermissionTo(Permission::all());

        $this->command->info('Created super-admin role with all permissions.');
    }

    private function createCashierRole(): void
    {
        $maxReturnAmount = config('pos.constraints.cashier.max_return_amount', 1000000);
        $currencySymbol = config('pos.currency.symbol', 'Rp');

        $cashier = Role::updateOrCreate(
            ['name' => 'cashier'],
            ['guard_name' => 'web']
        );

        $cashierPermissions = [
            'create_sale',
            'view_sale',
            'view_product',
            'view_inventory',
            'open_shift',
            'close_shift',
            'view_shift',
            'create_return', // Limited to {$currencySymbol}{$maxReturnAmount} via policies
            'apply_discount', // Basic discounts only
        ];

        $cashier->syncPermissions($cashierPermissions);
    }

    private function createSupervisorRole(): void
    {
        $maxStockAdjustment = config('pos.constraints.supervisor.max_stock_adjustment', 5);

        $supervisor = Role::updateOrCreate(
            ['name' => 'supervisor'],
            ['guard_name' => 'web']
        );

        // Supervisor has all cashier permissions plus additional ones
        $supervisorPermissions = [
            'create_sale',
            'void_sale',
            'view_sale',
            'view_product',
            'view_inventory',
            'open_shift',
            'close_shift',
            'view_shift',
            'create_return',
            'create_unlimited_return',
            'apply_discount',
            'approve_discount',
            'adjust_stock', // Limited to ±{$maxStockAdjustment} via policies
            'view_reports',
            'view_user',
            'view_role',
        ];

        $supervisor->syncPermissions($supervisorPermissions);
    }

    private function createAdminRole(): void
    {
        $admin = Role::updateOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );

        // Admin has all permissions
        $admin->givePermissionTo(Permission::all());
    }
}
