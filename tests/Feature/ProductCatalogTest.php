<?php

use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Tenant;
use Illuminate\Support\Facades\App;

use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    $this->tenant1 = Tenant::factory()->create(['code' => 'T001']);
    $this->tenant2 = Tenant::factory()->create(['code' => 'T002']);

    $this->outlet1 = Outlet::factory()->create(['tenant_id' => $this->tenant1->id]);
    $this->outlet2 = Outlet::factory()->create(['tenant_id' => $this->tenant2->id]);
});

it('automatically scopes product categories by tenant', function () {
    App::instance('current_tenant', $this->tenant1);

    $category1 = ProductCategory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'name' => 'Beverages T1',
    ]);

    $category2 = ProductCategory::factory()->create([
        'tenant_id' => $this->tenant2->id,
        'name' => 'Beverages T2',
    ]);

    // Should only see tenant1's category
    expect(ProductCategory::count())->toBe(1);
    expect(ProductCategory::first()->name)->toBe('Beverages T1');
});

it('automatically assigns tenant_id when creating category', function () {
    App::instance('current_tenant', $this->tenant1);

    $category = ProductCategory::create([
        'name' => 'Snacks',
        'code' => 'SNK001',
        'status' => 'active',
    ]);

    expect($category->tenant_id)->toBe($this->tenant1->id);
});

it('supports nested categories with parent-child relationships', function () {
    App::instance('current_tenant', $this->tenant1);

    $parent = ProductCategory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'name' => 'Beverages',
    ]);

    $child = ProductCategory::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'parent_id' => $parent->id,
        'name' => 'Soft Drinks',
    ]);

    expect($parent->children)->toHaveCount(1);
    expect($child->parent->name)->toBe('Beverages');
});

it('scopes products by tenant', function () {
    App::instance('current_tenant', $this->tenant1);

    Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'sku' => 'SKU-001',
    ]);

    Product::factory()->create([
        'tenant_id' => $this->tenant2->id,
        'sku' => 'SKU-002',
    ]);

    expect(Product::count())->toBe(1);
    expect(Product::first()->sku)->toBe('SKU-001');
});

it('enforces unique sku per tenant', function () {
    App::instance('current_tenant', $this->tenant1);

    Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'sku' => 'SKU-COKE',
    ]);

    // Same SKU in different tenant should work
    Product::factory()->create([
        'tenant_id' => $this->tenant2->id,
        'sku' => 'SKU-COKE',
    ]);

    assertDatabaseCount('products', 2);
});

it('calculates tax-exclusive price correctly', function () {
    App::instance('current_tenant', $this->tenant1);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'price_incl' => 11100.00,
        'tax_rate' => 11.00,
    ]);

    // price_excl = price_incl / (1 + tax_rate/100)
    // 11100 / 1.11 = 10000
    expect($product->price_excl)->toBe(10000.00);
    expect($product->tax_amount)->toBe(1100.00);
});

it('filters active products', function () {
    App::instance('current_tenant', $this->tenant1);

    Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'status' => 'active',
    ]);

    Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'status' => 'archived',
    ]);

    expect(Product::active()->count())->toBe(1);
});

it('creates product variants with unique codes', function () {
    App::instance('current_tenant', $this->tenant1);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
    ]);

    $variant1 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'code' => 'VAR-001',
        'barcode' => '8991001234567',
    ]);

    $variant2 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'code' => 'VAR-002',
        'barcode' => '8991001234568',
    ]);

    expect($product->variants)->toHaveCount(2);
});

it('uses price override when set on variant', function () {
    App::instance('current_tenant', $this->tenant1);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'price_incl' => 5000,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_override_incl' => 7000,
    ]);

    expect($variant->effective_price)->toBe(7000.00);
});

it('falls back to product price when no override', function () {
    App::instance('current_tenant', $this->tenant1);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant1->id,
        'price_incl' => 5000,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_override_incl' => null,
    ]);

    expect($variant->effective_price)->toBe(5000.00);
});

it('enforces unique barcode globally', function () {
    $product1 = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
    $variant1 = ProductVariant::factory()->create([
        'product_id' => $product1->id,
        'barcode' => '8991001234567',
    ]);

    $product2 = Product::factory()->create(['tenant_id' => $this->tenant2->id]);

    // Same barcode should fail
    expect(fn () => ProductVariant::factory()->create([
        'product_id' => $product2->id,
        'barcode' => '8991001234567',
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
