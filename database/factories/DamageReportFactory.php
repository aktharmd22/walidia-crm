<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DamageReport>
 */
class DamageReportFactory extends Factory
{
    protected $model = DamageReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'discovered_at' => now(),
            'description' => fake()->sentence(10),
            'estimated_cost' => 2500,
            'deduct_from_deposit' => true,
            'inspection_status' => 'pending',
        ];
    }
}
