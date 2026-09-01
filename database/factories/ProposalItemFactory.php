<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProposalItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalItem>
 */
class ProposalItemFactory extends Factory
{
    protected $model = ProposalItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'charter',
            'category' => 'yacht_fee',
            'description_en' => 'Full day charter',
            'quantity' => 1,
            'unit_price' => 40000,
            'tax_rate' => 5,
            'tax_treatment' => 'standard',
            'tax_amount' => 2000,
            'line_total' => 42000,
        ];
    }
}
