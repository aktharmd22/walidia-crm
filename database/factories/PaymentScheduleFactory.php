<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentSchedule>
 */
class PaymentScheduleFactory extends Factory
{
    protected $model = PaymentSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Charter payment plan',
            'total_amount' => 42000,
            'currency' => 'AED',
            'status' => 'open',
        ];
    }
}
