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
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'barcode' => fake()->unique()->ean13(),
            'price' => fake()->randomFloat(2, 1, 1000),
            'cost_price' => fake()->randomFloat(2, 0.5, 500),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'min_stock_level' => fake()->numberBetween(1, 10),
            'unit' => fake()->randomElement(['pcs', 'kg', 'g', 'l', 'ml']),
            'is_active' => true,
            'track_stock' => true,
            'product_category_id' => \App\Models\ProductCategory::factory(),
        ];
    }
}
