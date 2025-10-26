<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $businessTypes = ['Store', 'Outlet', 'Shop', 'Market', 'Cafe', 'Restaurant'];
        $locations = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Bali', 'Medan'];

        return [
            'code' => sprintf('TNT%03d', $counter++),
            'name' => fake()->company().' '.$businessTypes[array_rand($businessTypes)].' '.$locations[array_rand($locations)],
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'allow_negative_stock' => false,
                'cash_rounding' => 100,
                'price_includes_tax' => true,
                'default_tax_rate' => 11.0,
            ],
        ];
    }
}
