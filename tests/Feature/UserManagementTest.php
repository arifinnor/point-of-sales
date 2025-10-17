<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions and roles
    Permission::create(['name' => 'view_user']);
    Permission::create(['name' => 'manage_user']);

    $adminRole = Role::create(['name' => 'admin']);
    $supervisorRole = Role::create(['name' => 'supervisor']);
    $cashierRole = Role::create(['name' => 'cashier']);

    $adminRole->givePermissionTo(['view_user', 'manage_user']);
    $supervisorRole->givePermissionTo(['view_user']);
});

describe('User Management Index', function () {
    it('allows admin to view users list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $users = User::factory(5)->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 6) // 5 created + 1 admin
        );
    });

    it('prevents unauthorized users from viewing users list', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    });

    it('allows filtering users by search term', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $john = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $jane = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($admin)->get(route('users.index', ['search' => 'John']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
        );
    });

    it('allows filtering users by role', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');

        $response = $this->actingAs($admin)->get(route('users.index', ['role' => 'supervisor']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Index')
            ->has('users.data', 1)
        );
    });
});

describe('User Management Create', function () {
    it('allows admin to view create user form', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Create')
            ->has('roles')
        );
    });

    it('prevents non-admin from viewing create user form', function () {
        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $response = $this->actingAs($supervisor)->get(route('users.create'));

        $response->assertForbidden();
    });
});

describe('User Management Store', function () {
    it('allows admin to create a new user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

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

        $user = User::where('email', 'newuser@example.com')->first();
        expect($user->hasRole('cashier'))->toBeTrue();
    });

    it('validates required fields when creating user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('validates unique email when creating user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

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
        $admin = User::factory()->create();
        $admin->assignRole('admin');

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
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('cashier');

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Show')
            ->where('user.id', $user->id)
            ->where('user.name', $user->name)
        );
    });

    it('prevents unauthorized user from viewing user details', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.show', $otherUser));

        $response->assertForbidden();
    });
});

describe('User Management Edit', function () {
    it('allows admin to view edit user form', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.edit', $user));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('users/Edit')
            ->where('user.id', $user->id)
            ->has('roles')
        );
    });

    it('prevents non-admin from viewing edit user form', function () {
        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $user = User::factory()->create();

        $response = $this->actingAs($supervisor)->get(route('users.edit', $user));

        $response->assertForbidden();
    });
});

describe('User Management Update', function () {
    it('allows admin to update user information', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
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
        expect($user->hasRole('supervisor'))->toBeTrue();
    });

    it('allows admin to update user password', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
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
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

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
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('prevents user from deleting themselves', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('prevents non-admin from deleting users', function () {
        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');

        $user = User::factory()->create();

        $response = $this->actingAs($supervisor)->delete(route('users.destroy', $user));

        $response->assertForbidden();
    });
});
