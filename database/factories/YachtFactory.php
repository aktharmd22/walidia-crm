<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Yacht;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Yacht>
 */
class YachtFactory extends Factory
{
    protected $model = Yacht::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $loa = fake()->randomFloat(1, 30, 50);
        $static = (int) round($loa * 2.4);

        return [
            // Not unique: two hulls really can share a name across owners.
            'name' => fake()->randomElement([
                'Serenity IX', 'Northern Star', 'Azure Dawn', 'Blue Meridian', 'Desert Pearl',
                'Silver Horizon', 'Arabian Sky', 'Majlis', 'Falcon Wind', 'Pearl of Abu Dhabi',
                'Gulf Mirage', 'Sahara Blue', 'Marina Belle', 'Emirati Spirit', 'Oryx',
            ]).' '.fake()->randomElement(['', 'II', 'III', 'V', 'IX', 'X']),
            'builder' => fake()->randomElement(['Benetti', 'Azimut', 'Sunseeker', 'Majesty', 'Ferretti', 'Gulf Craft', 'Sanlorenzo']),
            'model' => fake()->numberBetween(100, 175).' Series',
            'year_built' => fake()->numberBetween(2012, 2024),
            'loa_m' => $loa,
            'beam_m' => round($loa * 0.21, 2),
            'draft_m' => round($loa * 0.06, 2),
            'gross_tonnage' => (int) round($loa * 12),
            'engines' => '2 x MTU '.fake()->randomElement(['12V 2000 M96', '16V 2000 M96L']),
            'engine_hours' => fake()->numberBetween(200, 4500),
            'cruising_speed_kn' => fake()->numberBetween(11, 16),
            'max_speed_kn' => fake()->numberBetween(18, 26),
            'capacity_static' => $static,
            'capacity_cruising' => (int) round($static * 0.5),
            'cabins' => fake()->numberBetween(4, 8),
            'berths' => fake()->numberBetween(8, 14),
            'crew_count' => fake()->numberBetween(5, 12),
            'flag_country' => fake()->randomElement(['United Arab Emirates', 'Cayman Islands', 'Malta', 'Marshall Islands']),
            'registration_no' => strtoupper(fake()->bothify('AUH-####')),
            'is_charter_fleet' => true,
            'is_for_sale' => false,
            'is_managed' => false,
            'status' => 'active',
        ];
    }

    public function forSale(): static
    {
        return $this->state(fn (): array => ['is_for_sale' => true]);
    }

    public function managed(): static
    {
        return $this->state(fn (): array => ['is_managed' => true]);
    }
}
