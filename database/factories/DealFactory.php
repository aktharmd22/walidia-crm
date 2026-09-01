<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->name().' - charter',
            // Superyacht scale: a day charter runs to six figures in AED.
            'value' => fake()->randomFloat(2, 45000, 850000),
            'currency' => 'AED',
            'expected_close_date' => now()->addDays(fake()->numberBetween(5, 60)),
            'status' => 'open',
            'stage_entered_at' => now(),
        ];
    }
}
