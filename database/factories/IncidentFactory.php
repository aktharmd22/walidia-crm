<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'guest_injury',
            'severity' => 'minor',
            'occurred_at' => now(),
            'description' => fake()->sentence(12),
            'status' => 'open',
        ];
    }
}
