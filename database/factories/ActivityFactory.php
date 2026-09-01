<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['type' => 'note', 'summary' => fake()->sentence(4), 'occurred_at' => now()];
    }
}
