<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce tenant context for authenticated users
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Get the current tenant for the user
        $tenant = $user->currentTenant();

        // If no tenant found but user has tenants, set the first/default one
        if (! $tenant && $user->tenants()->exists()) {
            $tenant = $user->tenants()->wherePivot('is_default', true)->first()
                ?? $user->tenants()->first();

            if ($tenant) {
                session(['current_tenant_id' => $tenant->id]);
            }
        }

        // If still no tenant and user is not super admin, reject access
        if (! $tenant && ! $user->canAccessAllTenants()) {
            abort(403, 'No tenant access. Please contact your administrator.');
        }

        // Set tenant in app container for global access
        if ($tenant) {
            App::instance('current_tenant', $tenant);

            // Set tenant ID for Spatie Permission teams feature
            setPermissionsTeamId($tenant->id);

            // Also store in request attributes for easy access
            $request->attributes->set('tenant', $tenant);
        }

        return $next($request);
    }
}
