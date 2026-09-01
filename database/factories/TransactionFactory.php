<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agreed_price' => 11800000,
            'currency' => 'EUR',
            'deposit_amount' => 1180000,
            'balance_amount' => 10620000,
            'contract_type' => 'myba',
            'contract_signed_on' => now()->toDateString(),
            'expected_closing_on' => now()->addMonth()->toDateString(),
            'aml_cleared' => false,
            'status' => 'under_contract',
        ];
    }
}
