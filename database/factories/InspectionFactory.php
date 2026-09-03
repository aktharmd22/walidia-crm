<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inspection>
 */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'listing',
            'inspected_at' => now(),
            'hull_condition' => 4,
            'engine_condition' => 4,
            'interior_condition' => 5,
            'systems_condition' => 4,
            'outcome' => 'clear',
            'status' => 'completed',
        ];
    }
}
