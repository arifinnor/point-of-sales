<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;
        static $barcodeCounter = 8991000000000;

        $sizes = ['Small', 'Medium', 'Large', 'XL', '250ml', '500ml', '1L', 'Regular'];

        return [
            'product_id' => \App\Models\Product::factory(),
            'code' => 'VAR'.sprintf('%06d', $counter++),
            'name' => $sizes[array_rand($sizes)],
            'barcode' => (string) $barcodeCounter++,
            'price_override_incl' => null,
        ];
    }

    public function withPriceOverride(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price_override_incl' => $price,
        ]);
    }

    public function withoutBarcode(): static
    {
        return $this->state(fn (array $attributes) => [
            'barcode' => null,
        ]);
    }
}
