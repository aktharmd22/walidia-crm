<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtCharterProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtCharterProfile>
 */
class YachtCharterProfileFactory extends Factory
{
    protected $model = YachtCharterProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['hourly_rate' => 4500, 'full_day_rate' => 38000, 'currency' => 'AED', 'min_hours' => 4, 'is_bookable' => true];
    }
}
