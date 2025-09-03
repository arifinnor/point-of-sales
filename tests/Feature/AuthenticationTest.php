<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles and permissions
    $this->superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);
    $this->cashierRole = Role::create(['name' => 'cashier', 'guard_name' => 'web']);
    $this->staffRole = Role::create(['name' => 'staff', 'guard_name' => 'web']);

    // Create permissions
    $this->viewUsersPermission = Permission::create(['name' => 'view users', 'guard_name' => 'web']);
    $this->viewRolesPermission = Permission::create(['name' => 'view_role', 'guard_name' => 'web']);
    $this->viewPermissionsPermission = Permission::create(['name' => 'view_permission', 'guard_name' => 'web']);
    $this->viewDashboardPermission = Permission::create(['name' => 'view dashboard', 'guard_name' => 'web']);

    // Assign permissions to roles
    $this->superAdminRole->givePermissionTo([
        $this->viewUsersPermission,
        $this->viewRolesPermission,
        $this->viewPermissionsPermission,
        $this->viewDashboardPermission,
    ]);

    $this->managerRole->givePermissionTo([
        $this->viewUsersPermission,
        $this->viewDashboardPermission,
    ]);

    $this->cashierRole->givePermissionTo([
        $this->viewDashboardPermission,
    ]);

    $this->staffRole->givePermissionTo([
        $this->viewDashboardPermission,
    ]);
});

afterEach(function () {
    // Clean up roles and permissions
    Role::query()->delete();
    Permission::query()->delete();
});

describe('Authentication', function () {
    it('can create users with factory', function () {
        $user = User::factory()->create();

        expect($user)->toBeInstanceOf(User::class);
        expect($user->name)->not->toBeEmpty();
        expect($user->email)->not->toBeEmpty();
        expect($user->password)->not->toBeEmpty();
    });

    it('can authenticate users', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        expect($this->isAuthenticated())->toBeTrue();
        expect(auth()->check())->toBeTrue();
        expect(auth()->user())->toBe($user);
    });
});

describe('Role Management', function () {
    it('can assign roles to users', function () {
        $user = User::factory()->create();

        $user->assignRole($this->superAdminRole);

        expect($user->hasRole('super_admin'))->toBeTrue();
        expect($user->hasRole($this->superAdminRole))->toBeTrue();
    });

    it('can remove roles from users', function () {
        $user = User::factory()->create();
        $user->assignRole($this->superAdminRole);

        $user->removeRole($this->superAdminRole);

        expect($user->hasRole('super_admin'))->toBeFalse();
    });

    it('can check if user has specific role', function () {
        $user = User::factory()->create();
        $user->assignRole($this->managerRole);

        expect($user->hasRole('manager'))->toBeTrue();
        expect($user->hasRole('super_admin'))->toBeFalse();
    });

    it('can check if user has any of multiple roles', function () {
        $user = User::factory()->create();
        $user->assignRole($this->cashierRole);

        expect($user->hasAnyRole(['cashier', 'manager']))->toBeTrue();
        expect($user->hasAnyRole(['super_admin', 'manager']))->toBeFalse();
    });
});

describe('Permission Management', function () {
    it('can assign permissions to users', function () {
        $user = User::factory()->create();

        $user->givePermissionTo($this->viewUsersPermission);

        expect($user->hasPermissionTo('view users'))->toBeTrue();
        expect($user->hasPermissionTo($this->viewUsersPermission))->toBeTrue();
    });

    it('can remove permissions from users', function () {
        $user = User::factory()->create();
        $user->givePermissionTo($this->viewUsersPermission);

        $user->revokePermissionTo($this->viewUsersPermission);

        expect($user->hasPermissionTo('view users'))->toBeFalse();
    });

    it('can check if user has specific permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo($this->viewDashboardPermission);

        expect($user->hasPermissionTo('view dashboard'))->toBeTrue();
        expect($user->hasPermissionTo('view users'))->toBeFalse();
    });

    it('can check if user has any of multiple permissions', function () {
        $user = User::factory()->create();
        $user->givePermissionTo($this->viewDashboardPermission);

        expect($user->hasAnyPermission(['view dashboard', 'view users']))->toBeTrue();
        expect($user->hasAnyPermission(['view roles', 'view permissions']))->toBeFalse();
    });
});

describe('Role-Based Access Control', function () {
    it('super admin has access to all permissions', function () {
        $user = User::factory()->create();
        $user->assignRole($this->superAdminRole);

        expect($user->hasPermissionTo('view users'))->toBeTrue();
        expect($user->hasPermissionTo('view_role'))->toBeTrue();
        expect($user->hasPermissionTo('view_permission'))->toBeTrue();
        expect($user->hasPermissionTo('view dashboard'))->toBeTrue();
    });

    it('manager has limited access', function () {
        $user = User::factory()->create();
        $user->assignRole($this->managerRole);

        expect($user->hasPermissionTo('view users'))->toBeTrue();
        expect($user->hasPermissionTo('view dashboard'))->toBeTrue();
        expect($user->hasPermissionTo('view_role'))->toBeFalse();
        expect($user->hasPermissionTo('view_permission'))->toBeFalse();
    });

    it('cashier has minimal access', function () {
        $user = User::factory()->create();
        $user->assignRole($this->cashierRole);

        expect($user->hasPermissionTo('view dashboard'))->toBeTrue();
        expect($user->hasPermissionTo('view users'))->toBeFalse();
        expect($user->hasPermissionTo('view_role'))->toBeFalse();
        expect($user->hasPermissionTo('view_permission'))->toBeFalse();
    });

    it('staff has minimal access', function () {
        $user = User::factory()->create();
        $user->assignRole($this->staffRole);

        expect($user->hasPermissionTo('view dashboard'))->toBeTrue();
        expect($user->hasPermissionTo('view users'))->toBeFalse();
        expect($user->hasPermissionTo('view_role'))->toBeFalse();
        expect($user->hasPermissionTo('view_permission'))->toBeFalse();
    });
});

describe('Middleware Protection', function () {
    it('can check authentication state', function () {
        expect($this->isAuthenticated())->toBeFalse();

        $user = User::factory()->create();
        $this->actingAs($user);

        expect($this->isAuthenticated())->toBeTrue();
    });
});

describe('User Factory', function () {
    it('creates users with valid data', function () {
        $user = User::factory()->create();

        expect($user)->toBeInstanceOf(User::class);
        expect($user->name)->not->toBeEmpty();
        expect($user->email)->not->toBeEmpty();
        expect($user->password)->not->toBeEmpty();
    });

    it('creates users with custom attributes', function () {
        $user = User::factory()->create([
            'name' => 'Custom Name',
            'email' => 'custom@example.com',
        ]);

        expect($user->name)->toBe('Custom Name');
        expect($user->email)->toBe('custom@example.com');
    });
});

describe('Resource Access Control', function () {
    it('only super admin can access Role resource', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($this->superAdminRole);

        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);

        $this->actingAs($superAdmin);
        expect(\App\Filament\Resources\Roles\RoleResource::canAccess())->toBeTrue();

        $this->actingAs($manager);
        expect(\App\Filament\Resources\Roles\RoleResource::canAccess())->toBeFalse();
    });

    it('only super admin can access Permission resource', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($this->superAdminRole);

        $cashier = User::factory()->create();
        $cashier->assignRole($this->cashierRole);

        $this->actingAs($superAdmin);
        expect(\App\Filament\Resources\Permissions\PermissionResource::canAccess())->toBeTrue();

        $this->actingAs($cashier);
        expect(\App\Filament\Resources\Permissions\PermissionResource::canAccess())->toBeFalse();
    });

    it('unauthenticated users cannot access resources', function () {
        expect(\App\Filament\Resources\Roles\RoleResource::canAccess())->toBeFalse();
        expect(\App\Filament\Resources\Permissions\PermissionResource::canAccess())->toBeFalse();
    });
});
