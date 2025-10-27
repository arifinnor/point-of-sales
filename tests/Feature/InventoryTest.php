<?php

use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tenant;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->tenant1 = Tenant::factory()->create(['code' => 'T001']);
    $this->tenant2 = Tenant::factory()->create(['code' => 'T002']);

    $this->outlet1 = Outlet::factory()->create(['tenant_id' => $this->tenant1->id]);
    $this->outlet2 = Outlet::factory()->create(['tenant_id' => $this->tenant2->id]);

    App::instance('current_tenant', $this->tenant1);

    $this->product = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
});

it('automatically scopes inventory by tenant', function () {
    $inventory1 = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
    ]);

    App::instance('current_tenant', $this->tenant2);
    $product2 = Product::factory()->create(['tenant_id' => $this->tenant2->id]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id]);

    $inventory2 = Inventory::factory()->create([
        'tenant_id' => $this->tenant2->id,
        'variant_id' => $variant2->id,
        'outlet_id' => $this->outlet2->id,
        'on_hand' => 30,
    ]);

    // Switch back to tenant1
    App::instance('current_tenant', $this->tenant1);

    // Should only see tenant1's inventory
    expect(Inventory::count())->toBe(1);
    expect(Inventory::first()->on_hand)->toBe(50);
});

it('auto-assigns tenant_id when creating inventory', function () {
    $inventory = Inventory::create([
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 100,
        'safety_stock' => 10,
    ]);

    expect($inventory->tenant_id)->toBe($this->tenant1->id);
});

it('enforces unique inventory per tenant+variant+outlet', function () {
    Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
    ]);

    // Duplicate should fail
    expect(fn () => Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

it('detects low stock correctly', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 5,
        'safety_stock' => 10,
    ]);

    expect($inventory->isLowStock())->toBeTrue();
});

it('detects available stock correctly', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
        'safety_stock' => 10,
    ]);

    expect($inventory->isAvailable(10))->toBeTrue();
    expect($inventory->isAvailable(60))->toBeFalse();
});

it('decrements stock correctly', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
    ]);

    $result = $inventory->decrementStock(10);

    expect($result)->toBeTrue();
    expect($inventory->fresh()->on_hand)->toBe(40);
});

it('prevents negative stock when not allowed', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 5,
    ]);

    $result = $inventory->decrementStock(10, allowNegative: false);

    expect($result)->toBeFalse();
    expect($inventory->fresh()->on_hand)->toBe(5);
});

it('allows negative stock when explicitly enabled', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 5,
    ]);

    $result = $inventory->decrementStock(10, allowNegative: true);

    expect($result)->toBeTrue();
    expect($inventory->fresh()->on_hand)->toBe(-5);
});

it('increments stock correctly', function () {
    $inventory = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
    ]);

    $inventory->incrementStock(20);

    expect($inventory->fresh()->on_hand)->toBe(70);
});

it('filters low stock inventory', function () {
    Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 5,
        'safety_stock' => 10,
    ]);

    $variant2 = ProductVariant::factory()->create(['product_id' => $this->product->id]);
    Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $variant2->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
        'safety_stock' => 10,
    ]);

    expect(Inventory::lowStock()->count())->toBe(1);
});

it('retrieves inventory for specific outlet', function () {
    $outlet2 = Outlet::factory()->create(['tenant_id' => $this->tenant1->id]);

    $inventory1 = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $this->outlet1->id,
        'on_hand' => 50,
    ]);

    $inventory2 = Inventory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'variant_id' => $this->variant->id,
        'outlet_id' => $outlet2->id,
        'on_hand' => 30,
    ]);

    $foundInventory = $this->variant->inventoryForOutlet($this->outlet1->id);

    expect($foundInventory->id)->toBe($inventory1->id);
    expect($foundInventory->on_hand)->toBe(50);
});
