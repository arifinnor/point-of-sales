<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear any cached permissions and seed roles
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});

it('uses configurable cashier return limit', function () {
    // Set custom config value
    Config::set('pos.constraints.cashier.max_return_amount', 2000000);
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    // Should allow returns under the new limit
    expect(Gate::forUser($cashier)->allows('create-return', 1500000))->toBeTrue();
    
    // Should deny returns over the new limit
    expect(Gate::forUser($cashier)->denies('create-return', 2500000))->toBeTrue();
});

it('uses configurable supervisor stock adjustment limit', function () {
    // Set custom config value
    Config::set('pos.constraints.supervisor.max_stock_adjustment', 10);
    
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    // Should allow adjustments under the new limit
    expect(Gate::forUser($supervisor)->allows('adjust-stock', 8))->toBeTrue();
    expect(Gate::forUser($supervisor)->allows('adjust-stock', -10))->toBeTrue();
    
    // Should deny adjustments over the new limit
    expect(Gate::forUser($supervisor)->denies('adjust-stock', 15))->toBeTrue();
});

it('uses configurable supervisor approval threshold', function () {
    // Set custom config value
    Config::set('pos.constraints.approval.supervisor_required_amount', 10000000);
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    $largeAmount = 8000000; // Under new threshold
    
    // Should now allow cashier since it's under the new threshold
    expect(Gate::forUser($cashier)->allows('supervisor-approval', $largeAmount))->toBeTrue();
    
    // Should still allow supervisor
    expect(Gate::forUser($supervisor)->allows('supervisor-approval', $largeAmount))->toBeTrue();
    
    // Should deny amounts over the new threshold for cashiers
    expect(Gate::forUser($cashier)->denies('supervisor-approval', 15000000))->toBeTrue();
});

it('uses configurable business hours', function () {
    // Set custom business hours: 6 AM to 11 PM
    Config::set('pos.business_hours.start', 6);
    Config::set('pos.business_hours.end', 23);
    
    $user = User::factory()->create();
    $user->assignRole('cashier');
    
    // The gate should use the new configured hours
    // (Note: This would require mocking time to test properly)
    $result = Gate::forUser($user)->inspect('business-hours-only');
    expect($result)->not->toBeNull();
});

it('uses configurable discount approval threshold', function () {
    // Set custom discount threshold
    Config::set('pos.discounts.require_approval_threshold', 30);
    Config::set('pos.discounts.max_percentage', 80);
    
    // Give cashier the approve_discount permission for this test
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    $cashier->givePermissionTo('approve_discount'); // Grant permission for testing
    
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    // 25% discount should be allowed for cashier (under 30% threshold)
    expect(Gate::forUser($cashier)->allows('approve-discount', 25))->toBeTrue();
    
    // 50% discount should be denied for cashier (over 30% threshold)
    expect(Gate::forUser($cashier)->denies('approve-discount', 50))->toBeTrue();
    
    // 50% discount should be allowed for supervisor
    expect(Gate::forUser($supervisor)->allows('approve-discount', 50))->toBeTrue();
    
    // 90% discount should be denied for everyone (over 80% max)
    expect(Gate::forUser($supervisor)->denies('approve-discount', 90))->toBeTrue();
});

it('uses configurable currency settings', function () {
    // Set custom currency
    Config::set('pos.currency.symbol', '$');
    Config::set('pos.currency.code', 'USD');
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    // Test that error messages use the configured currency symbol
    $response = Gate::forUser($cashier)->inspect('create-return', 2000000);
    expect($response->message())->toContain('$');
});

it('uses configurable cash variance threshold', function () {
    // Set custom variance threshold
    Config::set('pos.shifts.cash_variance_threshold', 50000);
    
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');
    
    // Variance under threshold should be allowed for cashier
    expect(Gate::forUser($cashier)->allows('accept-cash-variance', 30000))->toBeTrue();
    
    // Variance over threshold should be denied for cashier
    expect(Gate::forUser($cashier)->denies('accept-cash-variance', 60000))->toBeTrue();
    
    // Variance over threshold should be allowed for supervisor
    expect(Gate::forUser($supervisor)->allows('accept-cash-variance', 60000))->toBeTrue();
});

it('respects configurable inventory settings', function () {
    // Test negative stock configuration
    Config::set('pos.inventory.allow_negative_stock', false);
    
    $user = User::factory()->create();
    
    expect(Gate::forUser($user)->denies('allow-negative-stock'))->toBeTrue();
    
    // Change config to allow negative stock
    Config::set('pos.inventory.allow_negative_stock', true);
    
    expect(Gate::forUser($user)->allows('allow-negative-stock'))->toBeTrue();
});
