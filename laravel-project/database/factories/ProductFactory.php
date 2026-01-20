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
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 1, 1000),
            'stock' => fake()->numberBetween(0, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
