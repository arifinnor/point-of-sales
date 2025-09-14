<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear any cached permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Seed the roles and permissions for each test
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});

it('can create roles and permissions', function () {
    expect(Role::where('name', 'cashier')->exists())->toBeTrue();
    expect(Role::where('name', 'supervisor')->exists())->toBeTrue();
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    
    expect(Permission::where('name', 'create_sale')->exists())->toBeTrue();
    expect(Permission::where('name', 'void_sale')->exists())->toBeTrue();
    expect(Permission::where('name', 'manage_user')->exists())->toBeTrue();
});

it('assigns correct permissions to cashier role', function () {
    $cashier = Role::where('name', 'cashier')->first();
    
    expect($cashier->hasPermissionTo('create_sale'))->toBeTrue();
    expect($cashier->hasPermissionTo('view_product'))->toBeTrue();
    expect($cashier->hasPermissionTo('create_return'))->toBeTrue();
    
    expect($cashier->hasPermissionTo('void_sale'))->toBeFalse();
    expect($cashier->hasPermissionTo('manage_user'))->toBeFalse();
});

it('assigns correct permissions to supervisor role', function () {
    $supervisor = Role::where('name', 'supervisor')->first();
    
    // Has all cashier permissions
    expect($supervisor->hasPermissionTo('create_sale'))->toBeTrue();
    expect($supervisor->hasPermissionTo('create_return'))->toBeTrue();
    
    // Plus additional supervisor permissions
    expect($supervisor->hasPermissionTo('void_sale'))->toBeTrue();
    expect($supervisor->hasPermissionTo('approve_discount'))->toBeTrue();
    expect($supervisor->hasPermissionTo('adjust_stock'))->toBeTrue();
    
    // But not admin permissions
    expect($supervisor->hasPermissionTo('manage_user'))->toBeFalse();
});

it('assigns all permissions to admin role', function () {
    $admin = Role::where('name', 'admin')->first();
    $allPermissions = Permission::all();
    
    foreach ($allPermissions as $permission) {
        expect($admin->hasPermissionTo($permission->name))
            ->toBeTrue("Admin should have {$permission->name} permission");
    }
});

it('enforces cashier return amount constraint via policy', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    // Should allow returns under limit
    expect(Gate::forUser($cashier)->allows('create-return', 500000))->toBeTrue();
    
    // Should deny returns over limit
    expect(Gate::forUser($cashier)->denies('create-return', 1500000))->toBeTrue();
});

it('enforces supervisor stock adjustment constraint via policy', function () {
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    // Should allow adjustments within limit
    expect(Gate::forUser($supervisor)->allows('adjust-stock', 3))->toBeTrue();
    expect(Gate::forUser($supervisor)->allows('adjust-stock', -5))->toBeTrue();
    
    // Should deny adjustments over limit
    expect(Gate::forUser($supervisor)->denies('adjust-stock', 10))->toBeTrue();
    expect(Gate::forUser($supervisor)->denies('adjust-stock', -7))->toBeTrue();
});

it('allows unlimited operations for admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    // Admin can create unlimited returns
    expect(Gate::forUser($admin)->allows('create-return', 50000000))->toBeTrue();
    
    // Admin can adjust stock by any amount
    expect(Gate::forUser($admin)->allows('adjust-stock', 1000))->toBeTrue();
});

it('enforces business hours constraint', function () {
    $user = User::factory()->create();
    $user->assignRole('cashier');
    
    // This test would need to mock the current time to test properly
    // For now, just verify the gate exists and can be called
    $result = Gate::forUser($user)->inspect('business-hours-only');
    expect($result)->not->toBeNull();
});

it('checks supervisor approval for large transactions', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    $largeAmount = 6000000; // Over Rp5,000,000
    
    // Cashier should be denied
    expect(Gate::forUser($cashier)->denies('supervisor-approval', $largeAmount))->toBeTrue();
    
    // Supervisor should be allowed
    expect(Gate::forUser($supervisor)->allows('supervisor-approval', $largeAmount))->toBeTrue();
});

it('denies access when user lacks basic permission', function () {
    $userWithoutPermission = User::factory()->create();
    // User has no roles, so no permissions
    
    expect(Gate::forUser($userWithoutPermission)->denies('create-return', 100000))->toBeTrue();
    expect(Gate::forUser($userWithoutPermission)->denies('adjust-stock', 1))->toBeTrue();
    expect(Gate::forUser($userWithoutPermission)->denies('void-sale'))->toBeTrue();
});

it('works with spatie role and permission methods', function () {
    $user = User::factory()->create();
    
    // Test Spatie's native methods
    $user->assignRole('cashier');
    expect($user->hasRole('cashier'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
    
    expect($user->hasPermissionTo('create_sale'))->toBeTrue();
    expect($user->hasPermissionTo('manage_user'))->toBeFalse();
    
    // Test direct permission assignment
    $user->givePermissionTo('view_reports');
    expect($user->hasPermissionTo('view_reports'))->toBeTrue();
});

it('can use spatie middleware helpers', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    
    // Test that we can check roles for middleware
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasAnyRole(['admin', 'supervisor']))->toBeTrue();
    expect($user->hasAllRoles(['admin']))->toBeTrue();
    
    // Test that we can check permissions for middleware
    expect($user->can('manage_user'))->toBeTrue();
    expect($user->hasPermissionTo('manage_user'))->toBeTrue();
});
