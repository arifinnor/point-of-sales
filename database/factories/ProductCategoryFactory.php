<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Beverages',
            'Snacks',
            'Food',
            'Electronics',
            'Household',
            'Personal Care',
            'Stationery',
            'Frozen Food',
            'Fresh Produce',
            'Dairy',
            'Bakery',
            'Health & Beauty',
        ];

        static $usedCategories = [];

        $availableCategories = array_diff($categories, $usedCategories);
        if (empty($availableCategories)) {
            $usedCategories = [];
            $availableCategories = $categories;
        }

        $categoryName = $availableCategories[array_rand($availableCategories)];
        $usedCategories[] = $categoryName;

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'parent_id' => null,
            'name' => $categoryName,
            'code' => strtoupper(substr($categoryName, 0, 3)).fake()->unique()->numberBetween(100, 999),
            'status' => 'active',
        ];
    }

    public function withParent(\App\Models\ProductCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'tenant_id' => $parent->tenant_id,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
