<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
{
    protected $model = Payout::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'seller',
            'payee_name' => fake()->name(),
            'amount' => 250000,
            'currency' => 'AED',
            'method' => 'bank_transfer',
            'due_on' => now()->addWeek()->toDateString(),
            'status' => 'pending',
        ];
    }
}
