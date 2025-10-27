<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Register>
 */
class RegisterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        return [
            'outlet_id' => \App\Models\Outlet::factory(),
            'name' => 'Register '.$counter++,
            'printer_profile_id' => null,
            'settings' => [],
        ];
    }
}
