<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtSaleProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtSaleProfile>
 */
class YachtSaleProfileFactory extends Factory
{
    protected $model = YachtSaleProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['asking_price' => 24500000, 'currency' => 'AED', 'price_visibility' => 'on_request'];
    }
}
