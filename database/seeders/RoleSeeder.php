<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management (Filament Shield format)
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',

            // Permission management (Filament Shield format)
            'view_permission',
            'view_any_permission',
            'create_permission',
            'update_permission',
            'delete_permission',
            'delete_any_permission',

            // Product management
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Category management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Order management
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'process orders',

            // Customer management
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',

            // Inventory management
            'view inventory',
            'update inventory',
            'adjust inventory',

            // Sales reports
            'view sales reports',
            'export sales reports',

            // Settings
            'view settings',
            'edit settings',

            // Dashboard
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $roles = [
            'super_admin' => $permissions,

            'manager' => [
                'view users',
                'view_role',
                'view_any_role',
                'view_permission',
                'view_any_permission',
                'view products',
                'create products',
                'edit products',
                'delete products',
                'view categories',
                'create categories',
                'edit categories',
                'delete categories',
                'view orders',
                'create orders',
                'edit orders',
                'delete orders',
                'process orders',
                'view customers',
                'create customers',
                'edit customers',
                'delete customers',
                'view inventory',
                'update inventory',
                'adjust inventory',
                'view sales reports',
                'export sales reports',
                'view settings',
                'edit settings',
                'view dashboard',
            ],

            'cashier' => [
                'view products',
                'view categories',
                'view orders',
                'create orders',
                'edit orders',
                'process orders',
                'view customers',
                'create customers',
                'edit customers',
                'view inventory',
                'view dashboard',
            ],

            'staff' => [
                'view products',
                'view categories',
                'view orders',
                'view customers',
                'view inventory',
                'view dashboard',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
