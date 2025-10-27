<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Coca Cola',
            'Pepsi',
            'Sprite',
            'Fanta',
            'Aqua',
            'Indomie Goreng',
            'Indomie Soto',
            'Chitato',
            'Teh Botol',
            'Good Day Cappuccino',
            'Milo',
            'Oreo',
            'Biskuat',
            'SilverQueen',
            'Tango',
            'Yakult',
            'Ultra Milk',
            'Dancow',
            'Bear Brand',
            'Kopi ABC',
        ];

        static $counter = 1000;

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'sku' => 'SKU'.sprintf('%06d', $counter++),
            'name' => $products[array_rand($products)].' '.fake()->word(),
            'category_id' => null,
            'tax_rate' => 11.00, // PPN 11% Indonesia
            'price_incl' => fake()->randomFloat(2, 5000, 50000),
            'status' => 'active',
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withCategory(\App\Models\ProductCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
            'tenant_id' => $category->tenant_id,
        ]);
    }

    public function taxFree(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_rate' => 0.00,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
