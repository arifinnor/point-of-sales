<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $currentUser = auth()->user();
        $currentTenant = $currentUser->currentTenant();

        // Query base: if super admin, show all users; otherwise, filter by current tenant
        if ($currentUser->canAccessAllTenants()) {
            $query = User::query()->with('tenants');
        } else {
            // Regular users only see users from their current tenant
            $query = User::query()
                ->whereHas('tenants', function ($q) use ($currentTenant) {
                    $q->where('tenants.id', $currentTenant->id);
                });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        $users = $query->latest()
            ->paginate(15)
            ->withQueryString();

        // Load roles for each user in their proper tenant context
        $users->getCollection()->transform(function ($user) {
            // Get the user's default tenant for role context
            $userTenant = $user->tenants()->wherePivot('is_default', true)->first()
                ?? $user->tenants()->first();

            if ($userTenant) {
                // Temporarily switch to user's tenant context to load roles
                $originalTeamId = getPermissionsTeamId();
                setPermissionsTeamId($userTenant->id);

                // Reload roles in the correct tenant context
                $user->load('roles');

                // Restore original tenant context
                setPermissionsTeamId($originalTeamId);
            }

            return $user;
        });

        // Get roles for the current tenant context
        $roles = Role::all(['id', 'name']);

        return Inertia::render('users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $roles = Role::all(['id', 'name']);

        return Inertia::render('users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = auth()->user();
        $currentTenant = $currentUser->currentTenant();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        // Attach user to current tenant
        if ($currentTenant) {
            $user->tenants()->attach($currentTenant->id, ['is_default' => true]);
        }

        // Assign roles in the current tenant context
        if (isset($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        // Load roles in the user's tenant context
        $userTenant = $user->tenants()->wherePivot('is_default', true)->first()
            ?? $user->tenants()->first();

        if ($userTenant) {
            $originalTeamId = getPermissionsTeamId();
            setPermissionsTeamId($userTenant->id);

            $user->load('roles.permissions');

            setPermissionsTeamId($originalTeamId);
        }

        return Inertia::render('users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        // Load roles in the user's tenant context
        $userTenant = $user->tenants()->wherePivot('is_default', true)->first()
            ?? $user->tenants()->first();

        if ($userTenant) {
            $originalTeamId = getPermissionsTeamId();
            setPermissionsTeamId($userTenant->id);

            $user->load('roles');

            // Get roles for the user's tenant context
            $roles = Role::all(['id', 'name']);

            setPermissionsTeamId($originalTeamId);
        } else {
            $roles = collect();
        }

        return Inertia::render('users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Only update password if provided
        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        // Sync roles in the user's tenant context
        if (isset($validated['roles'])) {
            $userTenant = $user->tenants()->wherePivot('is_default', true)->first()
                ?? $user->tenants()->first();

            if ($userTenant) {
                $originalTeamId = getPermissionsTeamId();
                setPermissionsTeamId($userTenant->id);

                $user->syncRoles($validated['roles']);

                setPermissionsTeamId($originalTeamId);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
