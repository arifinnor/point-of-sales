<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class POSPolicy
{
    /**
     * Determine if user can create returns with amount constraint.
     */
    public function createReturn(User $user, float $amount = 0): Response
    {
        // Check basic permission first
        if (!$user->hasPermissionTo('create_return')) {
            return Response::deny('You do not have permission to create returns.');
        }

        // Cashiers have amount limit, others don't (if they have unlimited permission)
        $maxReturnAmount = config('pos.constraints.cashier.max_return_amount');
        $currencySymbol = config('pos.currency.symbol', 'Rp');
        
        if ($user->hasRole('cashier') && $amount > $maxReturnAmount) {
            return Response::deny("Cashiers can only process returns up to {$currencySymbol}" . number_format($maxReturnAmount) . ". Amount: {$currencySymbol}" . number_format($amount));
        }

        return Response::allow();
    }

    /**
     * Determine if user can adjust stock with quantity constraint.
     */
    public function adjustStock(User $user, int $quantity = 0): Response
    {
        // Check basic permission first
        if (!$user->hasPermissionTo('adjust_stock')) {
            return Response::deny('You do not have permission to adjust stock.');
        }

        // Supervisors have quantity limit, admins don't
        $maxStockAdjustment = config('pos.constraints.supervisor.max_stock_adjustment');
        
        if ($user->hasRole('supervisor') && abs($quantity) > $maxStockAdjustment) {
            return Response::deny("Supervisors can only adjust stock by ±{$maxStockAdjustment} units. Requested: {$quantity}");
        }

        return Response::allow();
    }

    /**
     * Determine if user can approve discounts with percentage constraint.
     */
    public function approveDiscount(User $user, float $discountPercentage = 0): Response
    {
        // Check basic permission first
        if (!$user->hasPermissionTo('approve_discount')) {
            return Response::deny('You do not have permission to approve discounts.');
        }

        // Check if discount requires approval based on configured threshold
        $approvalThreshold = config('pos.discounts.require_approval_threshold', 50);
        $maxDiscount = config('pos.discounts.max_percentage', 100);
        
        // Validate discount doesn't exceed maximum allowed
        if ($discountPercentage > $maxDiscount) {
            return Response::deny("Discount cannot exceed {$maxDiscount}%.");
        }

        // If discount is over threshold and user is not supervisor/admin, deny
        if ($discountPercentage > $approvalThreshold && !$user->hasRole(['supervisor', 'admin'])) {
            return Response::deny("Discounts over {$approvalThreshold}% require supervisor approval.");
        }

        return Response::allow();
    }

    /**
     * Determine if user can void sales (supervisors and admins only).
     */
    public function voidSale(User $user): Response
    {
        if (!$user->hasPermissionTo('void_sale')) {
            return Response::deny('You do not have permission to void sales. Contact your supervisor.');
        }

        return Response::allow();
    }

    /**
     * Determine if user can access specific outlet data.
     */
    public function accessOutlet(User $user, int $outletId): Response
    {
        // Basic permission check
        if (!$user->hasPermissionTo('view_outlet')) {
            return Response::deny('You do not have permission to view outlet information.');
        }

        // Additional outlet-specific constraints can be added here
        // For example: user can only access their assigned outlets
        
        return Response::allow();
    }

    /**
     * Determine if user can perform actions during active shift.
     */
    public function requiresActiveShift(User $user): Response
    {
        // This would check if user has an active shift
        // Implementation depends on your shift management system
        
        // For now, just check if they can open/close shifts
        if (!$user->hasPermissionTo('open_shift')) {
            return Response::deny('You must have shift management permissions.');
        }

        // In real implementation, you'd check:
        // - Is there an active shift for this user/register?
        // - Is the shift within business hours?
        // - etc.

        return Response::allow();
    }

    /**
     * Time-based restriction based on configured business hours.
     */
    public function businessHoursOnly(User $user): Response
    {
        $timezone = config('pos.business_hours.timezone', 'Asia/Jakarta');
        $startHour = config('pos.business_hours.start', 8);
        $endHour = config('pos.business_hours.end', 22);
        
        $currentHour = now($timezone)->hour;
        
        if ($currentHour < $startHour || $currentHour > $endHour) {
            return Response::deny("Sales operations are only allowed between {$startHour}:00 and {$endHour}:00.");
        }

        return Response::allow();
    }

    /**
     * Supervisor approval required for large transactions.
     */
    public function requiresSupervisorApproval(User $user, float $amount): Response
    {
        $approvalThreshold = config('pos.constraints.approval.supervisor_required_amount');
        $currencySymbol = config('pos.currency.symbol', 'Rp');
        
        // If transaction is over the configured threshold, require supervisor approval
        if ($amount > $approvalThreshold && !$user->hasRole(['supervisor', 'admin'])) {
            return Response::deny("Transactions over {$currencySymbol}" . number_format($approvalThreshold) . " require supervisor approval.");
        }

        return Response::allow();
    }

    /**
     * Check if inventory allows negative stock.
     */
    public function allowNegativeStock(User $user): Response
    {
        if (!config('pos.inventory.allow_negative_stock', false)) {
            return Response::deny('Negative stock is not allowed in this system.');
        }

        return Response::allow();
    }

    /**
     * Check cash variance threshold for shift closing.
     */
    public function acceptCashVariance(User $user, float $variance): Response
    {
        $threshold = config('pos.shifts.cash_variance_threshold', 10000);
        $currencySymbol = config('pos.currency.symbol', 'Rp');
        
        if (abs($variance) > $threshold && !$user->hasRole(['supervisor', 'admin'])) {
            return Response::deny("Cash variance of {$currencySymbol}" . number_format(abs($variance)) . " exceeds threshold of {$currencySymbol}" . number_format($threshold) . ". Supervisor approval required.");
        }

        return Response::allow();
    }

    /**
     * Check if opening float is required for shift.
     */
    public function requiresOpeningFloat(User $user): Response
    {
        if (config('pos.shifts.require_opening_float', true)) {
            return Response::allow(); // Requirement exists
        }

        return Response::deny('Opening float is not required in this configuration.');
    }
}
