<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trashcan>
 */
class TrashcanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag' => fake()->word(),
            'description' => fake()->sentence(),
            'location' => implode(', ',fake()->localCoordinates),
            'fill_level' => fake()->numberBetween(0,2),
            'lid_blocked' => fake()->boolean(),
        ];
    }
}
