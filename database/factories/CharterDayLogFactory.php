<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterDayLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterDayLog>
 */
class CharterDayLogFactory extends Factory
{
    protected $model = CharterDayLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type' => fake()->randomElement(['departure', 'arrival', 'note', 'request']),
            'occurred_at' => now(),
            'body' => fake()->sentence(8),
        ];
    }
}
