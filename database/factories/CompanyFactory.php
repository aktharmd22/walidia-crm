<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Al Mansouri Group', 'Gulf Horizon Concierge', 'Doha Marine Ventures',
            'Seychelles Blue DMC', 'Pearl Hospitality', 'Emirates Elite Travel',
            'Lusail Charter Partners', 'Riyadh Leisure Holdings', 'Muscat Bay Services',
        ]).' '.fake()->randomElement(['', 'Holdings', 'International', 'Group', 'Marine']);

        return [
            'legal_name' => $name.' LLC',
            'trade_name' => $name,
            'type' => fake()->randomElement(['corporate', 'dmc', 'concierge', 'charter_partner']),
            'email' => fake()->companyEmail(),
            'phone' => '+9712'.fake()->numberBetween(1000000, 9999999),
            'city' => fake()->randomElement(['Abu Dhabi', 'Dubai', 'Doha']),
            'country' => 'United Arab Emirates',
            'payment_terms_days' => fake()->randomElement([0, 14, 30]),
            'status' => 'active',
        ];
    }
}
