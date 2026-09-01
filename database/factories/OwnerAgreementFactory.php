<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OwnerAgreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerAgreement>
 */
class OwnerAgreementFactory extends Factory
{
    protected $model = OwnerAgreement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['type' => 'charter_revenue_share', 'revenue_share_model' => 'net', 'owner_share_pct' => 70, 'company_share_pct' => 30, 'statement_frequency' => 'monthly', 'status' => 'active'];
    }
}
