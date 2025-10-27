<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outlet>
 */
class OutletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $districts = ['Central', 'North', 'South', 'East', 'West'];
        $modes = ['pos', 'restaurant', 'minimarket'];

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'code' => sprintf('OUT%03d', $counter++),
            'name' => fake()->city().' '.$districts[array_rand($districts)].' Outlet',
            'address' => fake()->address(),
            'mode' => $modes[array_rand($modes)],
            'settings' => [],
        ];
    }

    public function pos(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'pos',
        ]);
    }

    public function restaurant(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'restaurant',
        ]);
    }

    public function minimarket(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'minimarket',
        ]);
    }
}
