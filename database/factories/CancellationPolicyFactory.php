<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CancellationPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CancellationPolicy>
 */
class CancellationPolicyFactory extends Factory
{
    protected $model = CancellationPolicy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'applies_to' => 'charter',
            'is_default' => false,
            'rules' => [['days_before' => 30, 'fee_pct' => 0], ['days_before' => 7, 'fee_pct' => 50], ['days_before' => 0, 'fee_pct' => 100]],
        ];
    }
}
