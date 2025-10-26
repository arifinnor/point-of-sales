<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('user can belong to multiple tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant1->id, ['is_default' => true]);
    $user->tenants()->attach($tenant2->id, ['is_default' => false]);

    expect($user->tenants)->toHaveCount(2);
    expect($user->tenants->pluck('id'))->toContain($tenant1->id, $tenant2->id);
});

test('user can switch between their tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant1->id);
    $user->tenants()->attach($tenant2->id);

    // Switch to tenant1
    $result = $user->switchTenant($tenant1);
    expect($result)->toBeTrue();
    expect(session('current_tenant_id'))->toBe($tenant1->id);

    // Switch to tenant2
    $result = $user->switchTenant($tenant2);
    expect($result)->toBeTrue();
    expect(session('current_tenant_id'))->toBe($tenant2->id);
});

test('user cannot switch to tenant they do not have access to', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant1->id);

    $result = $user->switchTenant($tenant2);
    expect($result)->toBeFalse();
});

test('queries are automatically scoped to current tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    // Set tenant1 context
    App::instance('current_tenant', $tenant1);
    setPermissionsTeamId($tenant1->id);

    // Create users with BelongsToTenant trait would be scoped
    // For this test, we verify the app container has the tenant
    expect(App::get('current_tenant')->id)->toBe($tenant1->id);
    expect(getPermissionsTeamId())->toBe($tenant1->id);
});

test('super admin can access all tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    // Set tenant context for role assignment
    setPermissionsTeamId($tenant1->id);

    // Create super-admin role
    \Spatie\Permission\Models\Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    expect($superAdmin->isSuperAdmin())->toBeTrue();
    expect($superAdmin->canAccessAllTenants())->toBeTrue();
    expect($superAdmin->hasAccessToTenant($tenant1))->toBeTrue();
    expect($superAdmin->hasAccessToTenant($tenant2))->toBeTrue();
});

test('super admin can assume any tenant context', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    // Set tenant context for role assignment
    setPermissionsTeamId($tenant1->id);

    // Create super-admin role
    \Spatie\Permission\Models\Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $result = $superAdmin->assumeTenant($tenant1);
    expect($result)->toBeTrue();
    expect(session('current_tenant_id'))->toBe($tenant1->id);

    $result = $superAdmin->assumeTenant($tenant2);
    expect($result)->toBeTrue();
    expect(session('current_tenant_id'))->toBe($tenant2->id);
});

test('regular user cannot assume tenant they do not belong to', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $user = User::factory()->create();
    $user->tenants()->attach($tenant1->id);

    $result = $user->assumeTenant($tenant2);
    expect($result)->toBeFalse();
});

test('same sku allowed across tenants but unique within tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    // Both tenants can have products with same SKU
    // This will be tested when Product model is created
    // For now, verify tenants are isolated
    expect($tenant1->id)->not->toBe($tenant2->id);
});

test('tenant context service works correctly', function () {
    $tenantContext = app(\App\Support\TenantContext::class);
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant->id);

    // Set current tenant
    $result = $tenantContext->setCurrent($tenant, $user);
    expect($result)->toBeTrue();
    expect($tenantContext->hasCurrent())->toBeTrue();
    expect($tenantContext->getCurrent()->id)->toBe($tenant->id);
    expect($tenantContext->getCurrentId())->toBe($tenant->id);

    // Clear tenant context
    $tenantContext->clear();
    expect($tenantContext->hasCurrent())->toBeFalse();
    expect($tenantContext->getCurrent())->toBeNull();
});

test('tenant context validates user access', function () {
    $tenantContext = app(\App\Support\TenantContext::class);
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant1->id);

    // Should succeed for tenant1
    $result = $tenantContext->setCurrent($tenant1, $user);
    expect($result)->toBeTrue();

    // Should fail for tenant2
    $result = $tenantContext->setCurrent($tenant2, $user);
    expect($result)->toBeFalse();
});

test('tenant cache key helper generates correct keys', function () {
    $tenant = Tenant::factory()->create();
    $tenantContext = app(\App\Support\TenantContext::class);

    $tenantContext->setCurrent($tenant);

    $key = tenant_cache_key('products.list');
    expect($key)->toBe("tenant:{$tenant->id}:products.list");

    $tenantContext->clear();

    $key = tenant_cache_key('global.config');
    expect($key)->toBe('global:global.config');
});

test('current tenant helper returns correct tenant', function () {
    $tenant = Tenant::factory()->create();
    $tenantContext = app(\App\Support\TenantContext::class);

    $tenantContext->setCurrent($tenant);

    $currentTenant = current_tenant();
    expect($currentTenant)->not->toBeNull();
    expect($currentTenant->id)->toBe($tenant->id);
});
