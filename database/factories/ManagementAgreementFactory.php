<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ManagementAgreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManagementAgreement>
 */
class ManagementAgreementFactory extends Factory
{
    protected $model = ManagementAgreement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scope' => 'full',
            'fee_model' => 'fixed',
            'monthly_fee' => 45000,
            'currency' => 'AED',
            'starts_on' => now()->subYear()->toDateString(),
            'ends_on' => now()->addYear()->toDateString(),
            'notice_days' => 90,
            'status' => 'active',
        ];
    }
}
