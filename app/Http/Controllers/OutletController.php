<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->canAccessAllTenants()) {
            // Superadmin: Show all outlets with tenant info
            $outlets = Outlet::withoutGlobalScope(TenantScope::class)
                ->with(['registers', 'tenant'])
                ->latest()
                ->get();
        } else {
            // Regular user: Show only current tenant's outlets
            $outlets = Outlet::with(['registers'])
                ->latest()
                ->get();
        }

        return Inertia::render('outlets/Index', [
            'outlets' => $outlets,
            'isSuperAdmin' => $user->canAccessAllTenants(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();

        if ($user->canAccessAllTenants()) {
            // Superadmin: Show tenant selector
            $tenants = Tenant::all(['id', 'name']);

            return Inertia::render('outlets/Create', [
                'tenants' => $tenants,
                'isSuperAdmin' => true,
            ]);
        }

        // Regular user: No tenant selector needed
        return Inertia::render('outlets/Create', [
            'isSuperAdmin' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Auto-assign tenant_id for regular users
        if (! $user->canAccessAllTenants()) {
            $validated['tenant_id'] = app()->get('current_tenant')->id;
        }

        Outlet::create($validated);

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outlet $outlet): Response
    {
        $user = auth()->user();

        // Load tenant relationship for superadmins
        if ($user->canAccessAllTenants()) {
            $outlet->load('tenant');
        }

        return Inertia::render('outlets/Edit', [
            'outlet' => $outlet,
            'isSuperAdmin' => $user->canAccessAllTenants(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        $validated = $request->validated();

        $outlet->update($validated);

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outlet $outlet): RedirectResponse
    {
        // Prevent deletion if outlet has registers
        if ($outlet->registers()->exists()) {
            return redirect()->route('outlets.index')
                ->with('error', 'Cannot delete outlet with existing registers. Please delete all registers first.');
        }

        $outlet->delete();

        return redirect()->route('outlets.index')
            ->with('success', 'Outlet deleted successfully.');
    }
}
