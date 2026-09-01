<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaintenanceJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceJob>
 */
class MaintenanceJobFactory extends Factory
{
    protected $model = MaintenanceJob::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => 'routine',
            'title' => fake()->sentence(4),
            'priority' => 'normal',
            'due_on' => now()->addWeeks(2)->toDateString(),
            'estimated_cost' => 12000,
            'currency' => 'AED',
            'blocks_charter' => false,
            'status' => 'open',
        ];
    }
}
