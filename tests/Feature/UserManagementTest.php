<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create a test tenant
    $tenant = \App\Models\Tenant::factory()->create(['name' => 'Test Tenant']);

    // Set tenant context
    setPermissionsTeamId($tenant->id);
    app()->instance('current_tenant', $tenant);

    // Store tenant in test context for later use
    $this->tenant = $tenant;

    // Create permissions and roles
    Permission::create(['name' => 'view_user']);
    Permission::create(['name' => 'manage_user']);

    $adminRole = Role::create(['name' => 'admin']);
    $supervisorRole = Role::create(['name' => 'supervisor']);
    $cashierRole = Role::create(['name' => 'cashier']);

    $adminRole->givePermissionTo(['view_user', 'manage_user']);
    $supervisorRole->givePermissionTo(['view_user']);

    // Helper function to create user with tenant
    $this->createUserWithTenant = function ($attributes = [], $roleName = null) use ($tenant) {
        $user = User::factory()->create($attributes);
        $user->tenants()->attach($tenant->id, ['is_default' => true]);

        if ($roleName) {
            setPermissionsTeamId($tenant->id);
            $user->assignRole($roleName);
        }

        return $user;
    };
});

describe('User Management Index', function () {
    it('allows admin to view users list', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        for ($i = 0; $i < 5; $i++) {
            ($this->createUserWithTenant)();
        }

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 6) // 5 created + 1 admin
        );
    });

    it('prevents unauthorized users from viewing users list', function () {
        $user = ($this->createUserWithTenant)();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    });

    it('allows filtering users by search term', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $john = ($this->createUserWithTenant)(['name' => 'John Doe', 'email' => 'john@example.com']);
        $jane = ($this->createUserWithTenant)(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($admin)->get(route('users.index', ['search' => 'John']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
        );
    });

    it('allows filtering users by role', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $supervisor = ($this->createUserWithTenant)([], 'supervisor');

        $cashier = ($this->createUserWithTenant)([], 'cashier');

        $response = $this->actingAs($admin)->get(route('users.index', ['role' => 'supervisor']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 1)
        );
    });
});

describe('User Management Create', function () {
    it('allows admin to view create user form', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Create')
            ->has('roles')
        );
    });

    it('prevents non-admin from viewing create user form', function () {
        $supervisor = ($this->createUserWithTenant)([], 'supervisor');

        $response = $this->actingAs($supervisor)->get(route('users.create'));

        $response->assertForbidden();
    });
});

describe('User Management Store', function () {
    it('allows admin to create a new user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        // Set tenant context to check role
        setPermissionsTeamId($this->tenant->id);
        $user = User::where('email', 'newuser@example.com')->first();
        expect($user->hasRole('cashier'))->toBeTrue();
    });

    it('validates required fields when creating user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $response = $this->actingAs($admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('validates unique email when creating user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $existingUser = ($this->createUserWithTenant)(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates password confirmation when creating user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors(['password']);
    });
});

describe('User Management Show', function () {
    it('allows authorized user to view user details', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $user = ($this->createUserWithTenant)([], 'cashier');

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Show')
            ->where('user.id', $user->id)
            ->where('user.name', $user->name)
        );
    });

    it('prevents unauthorized user from viewing user details', function () {
        $user = ($this->createUserWithTenant)();
        $otherUser = ($this->createUserWithTenant)();

        $response = $this->actingAs($user)->get(route('users.show', $otherUser));

        $response->assertForbidden();
    });
});

describe('User Management Edit', function () {
    it('allows admin to view edit user form', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $user = ($this->createUserWithTenant)();

        $response = $this->actingAs($admin)->get(route('users.edit', $user));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Edit')
            ->where('user.id', $user->id)
            ->has('roles')
        );
    });

    it('prevents non-admin from viewing edit user form', function () {
        $supervisor = ($this->createUserWithTenant)([], 'supervisor');

        $user = ($this->createUserWithTenant)();

        $response = $this->actingAs($supervisor)->get(route('users.edit', $user));

        $response->assertForbidden();
    });
});

describe('User Management Update', function () {
    it('allows admin to update user information', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $user = ($this->createUserWithTenant)([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['supervisor'],
        ];

        $response = $this->actingAs($admin)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User updated successfully.');

        $user->refresh();
        expect($user->name)->toBe('Updated Name');
        expect($user->email)->toBe('updated@example.com');

        // Set tenant context to check role
        setPermissionsTeamId($this->tenant->id);
        expect($user->hasRole('supervisor'))->toBeTrue();
    });

    it('allows admin to update user password', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $user = ($this->createUserWithTenant)();
        $originalPassword = $user->password;

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($admin)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        expect($user->password)->not->toBe($originalPassword);
    });

    it('validates unique email when updating user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $existingUser = ($this->createUserWithTenant)(['email' => 'existing@example.com']);
        $user = ($this->createUserWithTenant)(['email' => 'user@example.com']);

        $updateData = [
            'name' => $user->name,
            'email' => 'existing@example.com',
        ];

        $response = $this->actingAs($admin)->put(route('users.update', $user), $updateData);

        $response->assertSessionHasErrors(['email']);
    });
});

describe('User Management Delete', function () {
    it('allows admin to delete user', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $user = ($this->createUserWithTenant)();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('prevents user from deleting themselves', function () {
        $admin = ($this->createUserWithTenant)([], 'admin');

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('prevents non-admin from deleting users', function () {
        $supervisor = ($this->createUserWithTenant)([], 'supervisor');

        $user = ($this->createUserWithTenant)();

        $response = $this->actingAs($supervisor)->delete(route('users.destroy', $user));

        $response->assertForbidden();
    });
});

describe('Multi-Tenant Role Loading', function () {
    beforeEach(function () {
        // Clear any existing roles and permissions from parent beforeEach
        \DB::table('roles')->truncate();
        \DB::table('permissions')->truncate();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    });

    it('loads roles correctly for users in different tenants', function () {
        // Create tenants
        $tenant1 = \App\Models\Tenant::factory()->create(['name' => 'Tenant 1']);
        $tenant2 = \App\Models\Tenant::factory()->create(['name' => 'Tenant 2']);

        // Create super admin with access to all tenants
        $superAdmin = User::factory()->create(['email' => 'superadmin@example.com']);
        $superAdmin->tenants()->attach($tenant1->id, ['is_default' => true]);
        $superAdmin->tenants()->attach($tenant2->id);

        // Create permissions
        $viewUserPermission = Permission::create(['name' => 'view_user']);

        // Set tenant context and create super-admin role
        setPermissionsTeamId($tenant1->id);
        $superAdminRole = Role::create(['name' => 'super-admin', 'team_id' => null]);
        $superAdminRole->givePermissionTo('view_user');
        $superAdmin->assignRole('super-admin');

        // Create roles for tenant 1
        setPermissionsTeamId($tenant1->id);
        $adminRole1 = Role::create(['name' => 'admin']);
        $adminRole1->givePermissionTo('view_user');
        $cashierRole1 = Role::create(['name' => 'cashier']);

        // Create user in tenant 1
        $tenant1User = User::factory()->create(['email' => 'admin1@example.com']);
        $tenant1User->tenants()->attach($tenant1->id, ['is_default' => true]);
        $tenant1User->assignRole('admin');

        // Create roles for tenant 2
        setPermissionsTeamId($tenant2->id);
        $adminRole2 = Role::create(['name' => 'admin']);
        $adminRole2->givePermissionTo('view_user');
        $supervisorRole2 = Role::create(['name' => 'supervisor']);

        // Create user in tenant 2
        $tenant2User = User::factory()->create(['email' => 'admin2@example.com']);
        $tenant2User->tenants()->attach($tenant2->id, ['is_default' => true]);
        $tenant2User->assignRole('admin');

        // Set super admin context to tenant 1
        $superAdmin->switchTenant($tenant1);

        // Manually set app tenant context for the test
        app()->instance('current_tenant', $tenant1);
        setPermissionsTeamId($tenant1->id);

        // Refresh the user to ensure roles are loaded properly
        $superAdmin->refresh();
        $superAdmin->load('roles', 'permissions');

        // Act: Super admin views user list
        $response = $this->actingAs($superAdmin)->get(route('users.index'));

        // Assert: All users are visible with their respective roles
        $response->assertOk();
        $response->assertInertia(function ($page) {
            $page->component('users/Index')
                ->has('users.data')
                ->where('users.data', function ($users) {
                    $tenant2UserData = collect($users)->firstWhere('email', 'admin2@example.com');

                    // Verify tenant 2 user has roles loaded
                    return $tenant2UserData && ! empty($tenant2UserData['roles']);
                });
        });
    });

    it('regular users only see users from their own tenant', function () {
        // Create tenants
        $tenant1 = \App\Models\Tenant::factory()->create(['name' => 'Tenant 1']);
        $tenant2 = \App\Models\Tenant::factory()->create(['name' => 'Tenant 2']);

        // Create roles for tenant 1
        setPermissionsTeamId($tenant1->id);
        $adminRole1 = Role::create(['name' => 'admin']);
        Permission::create(['name' => 'view_user']);
        $adminRole1->givePermissionTo('view_user');

        // Create admin user in tenant 1
        $tenant1Admin = User::factory()->create(['email' => 'admin1@example.com']);
        $tenant1Admin->tenants()->attach($tenant1->id, ['is_default' => true]);
        $tenant1Admin->assignRole('admin');

        // Create cashier in tenant 1
        $tenant1Cashier = User::factory()->create(['email' => 'cashier1@example.com']);
        $tenant1Cashier->tenants()->attach($tenant1->id, ['is_default' => true]);

        // Create roles for tenant 2
        setPermissionsTeamId($tenant2->id);
        $adminRole2 = Role::create(['name' => 'admin']);

        // Create user in tenant 2
        $tenant2User = User::factory()->create(['email' => 'admin2@example.com']);
        $tenant2User->tenants()->attach($tenant2->id, ['is_default' => true]);
        $tenant2User->assignRole('admin');

        // Set tenant 1 admin context
        $tenant1Admin->switchTenant($tenant1);

        // Manually set app tenant context for the test
        app()->instance('current_tenant', $tenant1);
        setPermissionsTeamId($tenant1->id);

        // Refresh the user to ensure roles are loaded properly
        $tenant1Admin->refresh();
        $tenant1Admin->load('roles', 'permissions');

        // Act: Tenant 1 admin views user list
        $response = $this->actingAs($tenant1Admin)->get(route('users.index'));

        // Assert: Only tenant 1 users are visible
        $response->assertOk();
        $response->assertInertia(function ($page) {
            $page->component('users/Index')
                ->has('users.data', 2) // Only 2 users from tenant 1
                ->where('users.data', function ($users) {
                    // Verify tenant 2 user is NOT in the list
                    return collect($users)->where('email', 'admin2@example.com')->isEmpty();
                });
        });
    });

    it('creates new user in current tenant context', function () {
        // Create tenant
        $tenant = \App\Models\Tenant::factory()->create(['name' => 'Test Tenant']);

        // Set tenant context
        setPermissionsTeamId($tenant->id);

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $cashierRole = Role::create(['name' => 'cashier']);
        Permission::create(['name' => 'manage_user']);
        $adminRole->givePermissionTo('manage_user');

        // Create admin user
        $admin = User::factory()->create();
        $admin->tenants()->attach($tenant->id, ['is_default' => true]);
        $admin->assignRole('admin');

        // Set admin's tenant context
        $admin->switchTenant($tenant);

        // Manually set app tenant context for the test
        app()->instance('current_tenant', $tenant);
        setPermissionsTeamId($tenant->id);

        // Act: Admin creates a new user
        $userData = [
            'name' => 'New Cashier',
            'email' => 'newcashier@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['cashier'],
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $userData);

        // Assert: User is created and attached to the current tenant
        $response->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'newcashier@example.com')->first();
        expect($newUser)->not->toBeNull();
        expect($newUser->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue();

        // Verify role is assigned in tenant context
        setPermissionsTeamId($tenant->id);
        expect($newUser->hasRole('cashier'))->toBeTrue();
    });
});
