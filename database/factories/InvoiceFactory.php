<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'tax_invoice',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'place_of_supply' => 'United Arab Emirates',
            'tax_treatment' => 'standard',
            'currency' => 'AED',
            'subtotal' => 40000,
            'tax_amount' => 2000,
            'total' => 42000,
            'amount_due' => 42000,
            'status' => 'draft',
        ];
    }
}
