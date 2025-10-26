<?php

/**
 * Global helper functions for the POS application.
 */

use App\Support\TenantContext;

if (! function_exists('tenant_cache_key')) {
    /**
     * Generate a tenant-namespaced cache key.
     *
     * @param  string  $key  The base cache key
     * @return string The tenant-namespaced cache key
     */
    function tenant_cache_key(string $key): string
    {
        $tenantContext = app(TenantContext::class);
        $tenantId = $tenantContext->getCurrentId();

        if (! $tenantId) {
            // If no tenant context, return key with 'global' prefix
            return "global:{$key}";
        }

        return "tenant:{$tenantId}:{$key}";
    }
}

if (! function_exists('current_tenant')) {
    /**
     * Get the current tenant from context.
     */
    function current_tenant(): ?\App\Models\Tenant
    {
        $tenantContext = app(TenantContext::class);

        return $tenantContext->getCurrent();
    }
}
