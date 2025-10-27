<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $onHand = fake()->numberBetween(0, 100);

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'variant_id' => \App\Models\ProductVariant::factory(),
            'outlet_id' => \App\Models\Outlet::factory(),
            'on_hand' => $onHand,
            'safety_stock' => fake()->numberBetween(5, 20),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'on_hand' => fake()->numberBetween(0, 5),
            'safety_stock' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'on_hand' => 0,
            'safety_stock' => 10,
        ]);
    }

    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'on_hand' => fake()->numberBetween(100, 500),
            'safety_stock' => 20,
        ]);
    }
}
