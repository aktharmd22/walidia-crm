<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'system' => 'engines',
            'title' => 'Main engine service',
            'interval_days' => 180,
            'interval_engine_hours' => 250,
            'last_done_on' => now()->subMonths(5)->toDateString(),
            'next_due_on' => now()->addMonth()->toDateString(),
            'budget_cost' => 18000,
            'blocks_charter' => false,
            'is_active' => true,
        ];
    }
}
