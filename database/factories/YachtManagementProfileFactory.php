<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtManagementProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtManagementProfile>
 */
class YachtManagementProfileFactory extends Factory
{
    protected $model = YachtManagementProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['budget_annual' => 1800000, 'reporting_cadence' => 'monthly'];
    }
}
