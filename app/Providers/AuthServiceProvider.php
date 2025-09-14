<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\POSPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Register any model policies here if needed
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerGates();
    }

    /**
     * Register POS-specific gates that use our constraint policy.
     */
    private function registerGates(): void
    {
        // Return gates with amount constraints
        Gate::define('create-return', function (User $user, float $amount = 0) {
            return app(POSPolicy::class)->createReturn($user, $amount);
        });

        // Stock adjustment gates with quantity constraints
        Gate::define('adjust-stock', function (User $user, int $quantity = 0) {
            return app(POSPolicy::class)->adjustStock($user, $quantity);
        });

        // Discount approval gates
        Gate::define('approve-discount', function (User $user, float $discountPercentage = 0) {
            return app(POSPolicy::class)->approveDiscount($user, $discountPercentage);
        });

        // Void sale gate
        Gate::define('void-sale', function (User $user) {
            return app(POSPolicy::class)->voidSale($user);
        });

        // Outlet access gate
        Gate::define('access-outlet', function (User $user, int $outletId) {
            return app(POSPolicy::class)->accessOutlet($user, $outletId);
        });

        // Active shift requirement gate
        Gate::define('requires-active-shift', function (User $user) {
            return app(POSPolicy::class)->requiresActiveShift($user);
        });

        // Business hours gate
        Gate::define('business-hours-only', function (User $user) {
            return app(POSPolicy::class)->businessHoursOnly($user);
        });

        // Supervisor approval for large transactions
        Gate::define('supervisor-approval', function (User $user, float $amount) {
            return app(POSPolicy::class)->requiresSupervisorApproval($user, $amount);
        });

        // Simple permission-based gates (using Spatie's permissions)
        Gate::define('create-sale', function (User $user) {
            return $user->hasPermissionTo('create_sale');
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasPermissionTo('view_reports');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermissionTo('manage_user');
        });

        Gate::define('manage-products', function (User $user) {
            return $user->hasPermissionTo('manage_product');
        });

        // Role-based gates for convenience
        Gate::define('is-cashier', function (User $user) {
            return $user->hasRole('cashier');
        });

        Gate::define('is-supervisor', function (User $user) {
            return $user->hasRole('supervisor');
        });

        Gate::define('is-admin', function (User $user) {
            return $user->hasRole('admin');
        });

        // Additional configurable gates
        Gate::define('allow-negative-stock', function (User $user) {
            return app(POSPolicy::class)->allowNegativeStock($user);
        });

        Gate::define('accept-cash-variance', function (User $user, float $variance) {
            return app(POSPolicy::class)->acceptCashVariance($user, $variance);
        });

        Gate::define('requires-opening-float', function (User $user) {
            return app(POSPolicy::class)->requiresOpeningFloat($user);
        });

        // Compound gates for complex business logic
        Gate::define('can-process-sale', function (User $user, float $amount = 0) {
            // Must have permission AND active shift AND business hours
            return $user->hasPermissionTo('create_sale') &&
                   Gate::allows('requires-active-shift') &&
                   Gate::allows('business-hours-only') &&
                   Gate::allows('supervisor-approval', $amount);
        });
    }
}
