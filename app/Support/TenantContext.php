<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\App;

class TenantContext
{
    /**
     * Set the current tenant context.
     */
    public function setCurrent(Tenant $tenant, ?User $user = null): bool
    {
        // Validate user has access to this tenant if user is provided
        if ($user && ! $this->validateAccess($tenant, $user)) {
            return false;
        }

        // Store in session
        session(['current_tenant_id' => $tenant->id]);

        // Store in app container
        App::instance('current_tenant', $tenant);

        // Set for Spatie Permission teams feature
        setPermissionsTeamId($tenant->id);

        return true;
    }

    /**
     * Get the current tenant from context.
     */
    public function getCurrent(): ?Tenant
    {
        // Try to get from app container first
        if (App::has('current_tenant')) {
            return App::get('current_tenant');
        }

        // Fallback to session
        if (session()->has('current_tenant_id')) {
            $tenant = Tenant::find(session('current_tenant_id'));
            if ($tenant) {
                App::instance('current_tenant', $tenant);

                return $tenant;
            }
        }

        return null;
    }

    /**
     * Check if a current tenant is set.
     */
    public function hasCurrent(): bool
    {
        return $this->getCurrent() !== null;
    }

    /**
     * Clear the current tenant context.
     */
    public function clear(): void
    {
        // Clear session
        session()->forget('current_tenant_id');

        // Clear app container
        if (App::has('current_tenant')) {
            App::forgetInstance('current_tenant');
        }

        // Clear Spatie Permission team context
        setPermissionsTeamId(null);
    }

    /**
     * Validate that a user has access to a tenant.
     */
    public function validateAccess(Tenant $tenant, User $user): bool
    {
        // Super admins have access to all tenants
        if ($user->canAccessAllTenants()) {
            return true;
        }

        // Check if user belongs to this tenant
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Get the tenant ID from current context.
     */
    public function getCurrentId(): ?string
    {
        return $this->getCurrent()?->id;
    }
}
