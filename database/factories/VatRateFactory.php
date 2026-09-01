<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VatRate>
 */
class VatRateFactory extends Factory
{
    protected $model = VatRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('rate_????'), 'label' => 'Standard', 'rate_pct' => 5, 'treatment' => 'standard',
        ];
    }
}
