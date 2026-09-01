<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => 11800000,
            'currency' => 'EUR',
            'deposit_amount' => 1180000,
            'subject_to_survey' => true,
            'subject_to_sea_trial' => true,
            'proof_of_funds_received' => false,
            'valid_until' => now()->addWeeks(2)->toDateString(),
            'status' => 'draft',
        ];
    }
}
