<?php

namespace Database\Factories;

use App\Models\Trashcan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->limit(1)->first(),
            'trashcan_id' => Trashcan::inRandomOrder()->limit(1)->first(),
            'action' => fake()->word(),
            'timestamp' => now(),
            'result' => fake()->word(),
        ];
    }

    public function noUser(): static
    {
        return $this->state(fn(array $attributes):array => [
            'user_id' => null,
        ]);
    }

    public function noTrashcan(): static
    {
        return $this->state(fn(array $attributes):array => [
            'trashcan_id' => null,
        ]);
    }
}
