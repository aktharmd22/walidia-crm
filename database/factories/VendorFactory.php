<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'legal_name' => fake()->company().' LLC',
            'trade_name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'mobile' => '+9715'.fake()->numberBetween(10000000, 99999999),
            'payment_terms_days' => fake()->randomElement([0, 14, 30]),
            'is_approved' => true,
            'status' => 'active',
        ];
    }
}
