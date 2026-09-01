<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentScheduleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentScheduleItem>
 */
class PaymentScheduleItemFactory extends Factory
{
    protected $model = PaymentScheduleItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sequence' => 1,
            'label' => 'deposit',
            'percentage' => 50,
            'amount' => 21000,
            'due_at' => now()->addDays(3),
            'status' => 'due',
        ];
    }
}
