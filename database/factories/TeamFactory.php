<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Charter Sales', 'Brokerage', 'Fleet Operations']).' '.fake()->numberBetween(1, 9),
            'business_line' => fake()->randomElement(['charter', 'brokerage', 'management']),
            'is_active' => true,
        ];
    }
}
