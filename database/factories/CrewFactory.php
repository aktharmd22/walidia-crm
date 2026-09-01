<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Crew;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Crew>
 */
class CrewFactory extends Factory
{
    protected $model = Crew::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'full_name' => fake()->name(),
            'role' => fake()->randomElement(['captain', 'engineer', 'deckhand', 'steward', 'chef']),
            'employment_type' => fake()->randomElement(['employee', 'freelance']),
            'nationality' => fake()->randomElement(['Philippines', 'Ukraine', 'India', 'South Africa', 'United Kingdom']),
            'mobile' => '+9715'.fake()->numberBetween(10000000, 99999999),
            'day_rate' => fake()->randomElement([600, 850, 1200, 1800]),
            'currency' => 'AED',
            'status' => 'active',
        ];
    }
}
