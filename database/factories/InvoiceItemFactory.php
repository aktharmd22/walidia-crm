<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
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
