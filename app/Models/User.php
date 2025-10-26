<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the tenants that the user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenant')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * Get the user's current active tenant from session.
     */
    public function currentTenant(): ?Tenant
    {
        if (session()->has('current_tenant_id')) {
            return $this->tenants()->find(session('current_tenant_id'));
        }

        // Fallback to default tenant
        return $this->tenants()->wherePivot('is_default', true)->first()
            ?? $this->tenants()->first();
    }

    /**
     * Switch the user's active tenant.
     */
    public function switchTenant(Tenant $tenant): bool
    {
        if (! $this->hasAccessToTenant($tenant)) {
            return false;
        }

        session(['current_tenant_id' => $tenant->id]);

        // Set for Spatie Permission teams feature
        setPermissionsTeamId($tenant->id);

        return true;
    }

    /**
     * Check if the user has access to the given tenant.
     */
    public function hasAccessToTenant(Tenant $tenant): bool
    {
        if ($this->canAccessAllTenants()) {
            return true;
        }

        return $this->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Check if the user is a super admin with access to all tenants.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if the user can access all tenants (super admin).
     */
    public function canAccessAllTenants(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Assume a tenant context (for super admins).
     */
    public function assumeTenant(Tenant $tenant): bool
    {
        if (! $this->canAccessAllTenants()) {
            return false;
        }

        session(['current_tenant_id' => $tenant->id]);

        // Set for Spatie Permission teams feature
        setPermissionsTeamId($tenant->id);

        // TODO: Log tenant switch in audit trail

        return true;
    }
}
