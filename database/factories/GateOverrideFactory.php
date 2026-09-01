<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GateOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GateOverride>
 */
class GateOverrideFactory extends Factory
{
    protected $model = GateOverride::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => 'booking', 'subject_id' => 1, 'reason' => fake()->sentence(8), 'created_at' => now(),
        ];
    }
}
