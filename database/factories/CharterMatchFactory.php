<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterMatch>
 */
class CharterMatchFactory extends Factory
{
    protected $model = CharterMatch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score' => fake()->numberBetween(40, 95), 'reasons' => [], 'is_shortlisted' => false,
        ];
    }
}
