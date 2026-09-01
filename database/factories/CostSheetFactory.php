<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CostSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostSheet>
 */
class CostSheetFactory extends Factory
{
    protected $model = CostSheet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency' => 'AED',
            'status' => 'draft',
            'total_offer' => 0,
            'total_cost' => 0,
            'total_profit' => 0,
        ];
    }
}
