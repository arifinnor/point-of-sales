<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\UpdateRegisterRequest;
use App\Models\Outlet;
use App\Models\Register;
use App\Models\Scopes\TenantScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->canAccessAllTenants()) {
            // Superadmin: Show all registers with outlet and tenant info
            $registers = Register::withoutGlobalScope(TenantScope::class)
                ->with(['outlet.tenant'])
                ->latest()
                ->get();
        } else {
            // Regular user: Show only current tenant's registers
            $registers = Register::with(['outlet'])
                ->latest()
                ->get();
        }

        return Inertia::render('registers/Index', [
            'registers' => $registers,
            'isSuperAdmin' => $user->canAccessAllTenants(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = auth()->user();
        $outletId = $request->get('outlet_id');

        if ($user->canAccessAllTenants()) {
            // Superadmin: Show outlets from all tenants with tenant info
            $outlets = Outlet::withoutGlobalScope(TenantScope::class)
                ->with('tenant')
                ->get(['id', 'name', 'code', 'tenant_id']);
        } else {
            // Regular user: Show only current tenant's outlets
            $outlets = Outlet::all(['id', 'name', 'code']);
        }

        return Inertia::render('registers/Create', [
            'outlet_id' => $outletId,
            'outlets' => $outlets,
            'isSuperAdmin' => $user->canAccessAllTenants(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Register::create($validated);

        return redirect()->route('outlets.index')
            ->with('success', 'Register created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Register $register): Response
    {
        return Inertia::render('registers/Edit', [
            'register' => $register,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRegisterRequest $request, Register $register): RedirectResponse
    {
        $validated = $request->validated();

        $register->update($validated);

        return redirect()->route('outlets.index')
            ->with('success', 'Register updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Register $register): RedirectResponse
    {
        // TODO: Prevent deletion if register has active shift or recent sales
        // This will be implemented when Shift model is created

        $register->delete();

        return redirect()->route('outlets.index')
            ->with('success', 'Register deleted successfully.');
    }
}
