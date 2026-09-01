<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrewPayout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrewPayout>
 */
class CrewPayoutFactory extends Factory
{
    protected $model = CrewPayout::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'days' => 1, 'day_rate' => 850, 'gross' => 850, 'net' => 850, 'currency' => 'AED', 'status' => 'draft',
        ];
    }
}
