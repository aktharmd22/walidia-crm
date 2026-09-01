<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrewAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrewAssignment>
 */
class CrewAssignmentFactory extends Factory
{
    protected $model = CrewAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignable_type' => 'booking',
            'assignable_id' => 1,
            'role' => 'deckhand',
            'starts_at' => now()->addDays(14)->setTime(8, 0),
            'ends_at' => now()->addDays(14)->setTime(20, 0),
            'status' => 'confirmed',
        ];
    }
}
