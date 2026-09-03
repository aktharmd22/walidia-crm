<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoyaltyReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyReward>
 */
class LoyaltyRewardFactory extends Factory
{
    protected $model = LoyaltyReward::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'voucher',
            'value' => 5000,
            'currency' => 'AED',
            'code' => strtoupper(fake()->unique()->bothify('WY-####-??')),
            'valid_from' => now()->toDateString(),
            'expires_on' => now()->addYear()->toDateString(),
            'status' => 'issued',
        ];
    }
}
