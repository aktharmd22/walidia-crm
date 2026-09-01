<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mandate_type' => 'central',
            'asking_price' => 12500000,
            'currency' => 'EUR',
            'commission_rate' => 5,
            'agreement_signed_on' => now()->subMonths(2)->toDateString(),
            'agreement_expires_on' => now()->addMonths(10)->toDateString(),
            'requires_proof_of_funds' => true,
            'requires_nda' => true,
            'is_published' => true,
            'listed_on' => now()->subMonths(2)->toDateString(),
            'status' => 'active',
        ];
    }
}
