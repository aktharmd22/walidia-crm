<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OwnerStatement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerStatement>
 */
class OwnerStatementFactory extends Factory
{
    protected $model = OwnerStatement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'charter_revenue' => 320000,
            'management_fee' => 45000,
            'operating_costs' => 60000,
            'maintenance_costs' => 25000,
            'crew_costs' => 80000,
            'net_to_owner' => 110000,
            'currency' => 'AED',
            'status' => 'draft',
        ];
    }
}
