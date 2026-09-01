<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    protected $model = QuotationItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description_en' => 'Service', 'quantity' => 1, 'unit_price' => 10000, 'tax_amount' => 500, 'line_total' => 10500,
        ];
    }
}
