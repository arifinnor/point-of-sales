<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
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

describe('User Access Control', function () {
    it('only super admin can access User resource', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($this->superAdminRole);

        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);

        $cashier = User::factory()->create();
        $cashier->assignRole($this->cashierRole);

        $staff = User::factory()->create();
        $staff->assignRole($this->staffRole);

        // Super admin should have access
        $this->actingAs($superAdmin);
        expect(UserResource::canAccess())->toBeTrue();
        expect(UserResource::canViewAny())->toBeTrue();
        expect(UserResource::canCreate())->toBeTrue();

        // Other roles should not have access
        $this->actingAs($manager);
        expect(UserResource::canAccess())->toBeFalse();
        expect(UserResource::canViewAny())->toBeFalse();
        expect(UserResource::canCreate())->toBeFalse();

        $this->actingAs($cashier);
        expect(UserResource::canAccess())->toBeFalse();
        expect(UserResource::canViewAny())->toBeFalse();
        expect(UserResource::canCreate())->toBeFalse();

        $this->actingAs($staff);
        expect(UserResource::canAccess())->toBeFalse();
        expect(UserResource::canViewAny())->toBeFalse();
        expect(UserResource::canCreate())->toBeFalse();
    });

    it('unauthenticated users cannot access User resource', function () {
        expect(UserResource::canAccess())->toBeFalse();
        expect(UserResource::canViewAny())->toBeFalse();
        expect(UserResource::canCreate())->toBeFalse();
    });

    it('only super admin can access User pages', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($this->superAdminRole);

        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);

        // Super admin should have access to all pages
        $this->actingAs($superAdmin);
        expect(ListUsers::canAccess())->toBeTrue();
        expect(CreateUser::canAccess())->toBeTrue();

        // Manager should not have access
        $this->actingAs($manager);
        expect(ListUsers::canAccess())->toBeFalse();
        expect(CreateUser::canAccess())->toBeFalse();
    });
});

describe('User CRUD Operations', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($this->superAdminRole);
        $this->actingAs($this->superAdmin);
    });

    it('can list users', function () {
        $users = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users)
            ->assertSuccessful();
    });

    it('can search users by name', function () {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        Livewire::test(ListUsers::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$user1])
            ->assertCanNotSeeTableRecords([$user2]);
    });

    it('can search users by email', function () {
        $user1 = User::factory()->create(['email' => 'john@example.com']);
        $user2 = User::factory()->create(['email' => 'jane@example.com']);

        Livewire::test(ListUsers::class)
            ->searchTable('john@example.com')
            ->assertCanSeeTableRecords([$user1])
            ->assertCanNotSeeTableRecords([$user2]);
    });

    it('can filter users by email verification status', function () {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->unverified()->create();

        Livewire::test(ListUsers::class)
            ->filterTable('verified')
            ->assertCanSeeTableRecords([$verifiedUser])
            ->assertCanNotSeeTableRecords([$unverifiedUser]);
    });

    it('can create a new user', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    });

    it('can edit an existing user', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    });

    it('can view an existing user', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
            ->assertSuccessful();
    });

    it('can delete a user', function () {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    });
});

describe('User Form Validation', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($this->superAdminRole);
        $this->actingAs($this->superAdmin);
    });

    it('requires name when creating user', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => '',
                'email' => 'test@example.com',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    });

    it('requires email when creating user', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => '',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'required']);
    });

    it('requires valid email format', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    });

    it('requires unique email', function () {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    });

    it('requires password when creating user', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);
    });

    it('requires minimum password length', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123',
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'min']);
    });

    it('allows updating user without changing password', function () {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'password' => '', // Empty password should be allowed on edit
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    });

    it('allows updating email to same email for same user', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'test@example.com', // Same email should be allowed
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    });
});

describe('User Permission Enforcement', function () {
    it('prevents non-super-admin from accessing user list', function () {
        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);
        $this->actingAs($manager);

        Livewire::test(ListUsers::class)
            ->assertForbidden();
    });

    it('prevents non-super-admin from creating users', function () {
        $cashier = User::factory()->create();
        $cashier->assignRole($this->cashierRole);
        $this->actingAs($cashier);

        Livewire::test(CreateUser::class)
            ->assertForbidden();
    });

    it('prevents non-super-admin from editing users', function () {
        $staff = User::factory()->create();
        $staff->assignRole($this->staffRole);
        $this->actingAs($staff);

        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertForbidden();
    });

    it('prevents non-super-admin from viewing users', function () {
        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);
        $this->actingAs($manager);

        $user = User::factory()->create();

        Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
            ->assertForbidden();
    });

    it('prevents unauthenticated users from accessing any user operations', function () {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertForbidden();

        Livewire::test(CreateUser::class)
            ->assertForbidden();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertForbidden();

        Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
            ->assertForbidden();
    });
});

describe('User Table Actions', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($this->superAdminRole);
        $this->actingAs($this->superAdmin);
    });

    it('shows view and edit actions for super admin', function () {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertTableActionExists('view')
            ->assertTableActionExists('edit');
    });

    it('shows delete action in edit page for super admin', function () {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertActionExists('delete');
    });

    it('shows create action in list page for super admin', function () {
        Livewire::test(ListUsers::class)
            ->assertActionExists('create');
    });

    it('shows edit action in view page for super admin', function () {
        $user = User::factory()->create();

        Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
            ->assertActionExists('edit');
    });
});

describe('User Bulk Actions', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($this->superAdminRole);
        $this->actingAs($this->superAdmin);
    });

    it('allows bulk delete for super admin', function () {
        $users = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->callTableBulkAction('delete', $users);

        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
    });
});

describe('User Navigation', function () {
    it('shows users navigation item for super admin', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($this->superAdminRole);
        $this->actingAs($superAdmin);

        expect(UserResource::canAccess())->toBeTrue();
    });

    it('hides users navigation item for non-super-admin', function () {
        $manager = User::factory()->create();
        $manager->assignRole($this->managerRole);
        $this->actingAs($manager);

        expect(UserResource::canAccess())->toBeFalse();
    });
});
