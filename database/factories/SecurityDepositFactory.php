<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SecurityDeposit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeposit>
 */
class SecurityDepositFactory extends Factory
{
    protected $model = SecurityDeposit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => 10000,
            'currency' => 'AED',
            'method' => 'card_hold',
            'collected_at' => now(),
            'status' => 'held',
        ];
    }
}
