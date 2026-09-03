<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Valuation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Valuation>
 */
class ValuationFactory extends Factory
{
    protected $model = Valuation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'valued_on' => now()->toDateString(),
            'market_low' => 10500000,
            'market_high' => 13500000,
            'broker_valuation' => 12000000,
            'recommended_asking' => 12500000,
            'currency' => 'EUR',
            'pricing_decision' => 'proposed',
            'status' => 'draft',
        ];
    }
}
