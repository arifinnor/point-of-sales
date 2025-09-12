<?php

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Livewire\Livewire;

it('calculates totals when product is selected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 10.50,
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    // Use fillForm which should trigger the afterStateUpdated callbacks
    Livewire::test(CreateOrder::class)
        ->fillForm([
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertFormSet([
            'orderItems.0.product_name' => 'Test Product',
            'orderItems.0.unit_price' => 10.50,
            'orderItems.0.total_price' => 10.50,
            'subtotal' => '10.50',
            'tax_amount' => '1.05', // 10% of 10.50
            'total_amount' => '11.55', // 10.50 + 1.05
        ]);
});

it('calculates totals when quantity is changed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 10.50,
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ])
        ->assertFormSet([
            'orderItems.0.product_name' => 'Test Product',
            'orderItems.0.unit_price' => 10.50,
            'orderItems.0.total_price' => 21.00,
            'subtotal' => '21.00',
            'tax_amount' => '2.10', // 10% of 21.00
            'total_amount' => '23.10', // 21.00 + 2.10
        ]);
});

it('calculates totals with automatic 10% tax and discount', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 100.00,
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'discount_amount' => 5.00,
        ])
        ->assertFormSet([
            'subtotal' => '100.00',
            'tax_amount' => '10.00', // 10% of 100
            'total_amount' => '105.00', // 100 + 10 - 5
        ]);
});

it('calculates totals for multiple products', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = ProductCategory::factory()->create();
    $product1 = Product::factory()->create([
        'name' => 'Product 1',
        'price' => 10.00,
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    $product2 = Product::factory()->create([
        'name' => 'Product 2',
        'price' => 20.00,
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'orderItems' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertFormSet([
            'subtotal' => '40.00', // (10 * 2) + (20 * 1)
            'tax_amount' => '4.00', // 10% of 40.00
            'total_amount' => '44.00', // 40.00 + 4.00
        ]);
});
