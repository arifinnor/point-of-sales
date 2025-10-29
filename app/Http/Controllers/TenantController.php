<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    /**
     * Switch the current tenant context.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => 'required|string|exists:tenants,id',
        ]);

        $user = Auth::user();
        $tenant = Tenant::findOrFail($request->tenant_id);

        // Check if user has access to this tenant
        if (! $user->hasAccessToTenant($tenant)) {
            return redirect()->back()->withErrors([
                'tenant' => 'You do not have access to this tenant.',
            ]);
        }

        // Switch tenant context
        $success = $user->switchTenant($tenant);

        if (! $success) {
            return redirect()->back()->withErrors([
                'tenant' => 'Failed to switch tenant context.',
            ]);
        }

        return redirect()->back()->with('success', "Switched to {$tenant->name}");
    }
}
