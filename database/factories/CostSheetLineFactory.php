<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CostSheetLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostSheetLine>
 */
class CostSheetLineFactory extends Factory
{
    protected $model = CostSheetLine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phase' => 'quoted',
            'section' => 'revenue',
            'category' => 'yacht_fee',
            'description' => 'Full day charter',
            'quantity' => 1,
            'unit_price' => 40000,
            'amount' => 40000,
            'tax_rate' => 5,
            'tax_treatment' => 'standard',
            'tax_amount' => 2000,
        ];
    }
}
