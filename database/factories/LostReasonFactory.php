<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LostReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LostReason>
 */
class LostReasonFactory extends Factory
{
    protected $model = LostReason::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['label' => fake()->randomElement(['Price', 'Dates unavailable', 'Chose a competitor', 'No response']), 'is_active' => true];
    }
}
