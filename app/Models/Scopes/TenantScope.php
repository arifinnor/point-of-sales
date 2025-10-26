<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip if no current tenant is set
        if (! App::has('current_tenant')) {
            return;
        }

        $tenant = App::get('current_tenant');

        // Skip if tenant is null
        if (! $tenant) {
            return;
        }

        // Check if the model's table has a tenant_id column
        $table = $model->getTable();
        if (! Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        // Apply the tenant_id filter
        $builder->where($model->qualifyColumn('tenant_id'), $tenant->id);
    }
}
