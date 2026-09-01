<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_line' => 'charter',
            'issued_on' => now()->toDateString(),
            'currency' => 'AED',
            'subtotal' => 10000,
            'tax_amount' => 500,
            'total' => 10500,
            'status' => 'draft',
        ];
    }
}
