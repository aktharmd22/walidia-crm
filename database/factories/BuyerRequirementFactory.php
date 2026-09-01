<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BuyerRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuyerRequirement>
 */
class BuyerRequirementFactory extends Factory
{
    protected $model = BuyerRequirement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_min' => 8000000,
            'budget_max' => 15000000,
            'currency' => 'EUR',
            'loa_min' => 30,
            'loa_max' => 55,
            'year_from' => 2015,
            'status' => 'active',
        ];
    }
}
