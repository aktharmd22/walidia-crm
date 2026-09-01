<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterEnquiry>
 */
class CharterEnquiryFactory extends Factory
{
    protected $model = CharterEnquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'experience_type' => fake()->randomElement(['day_charter', 'sunset_cruise', 'overnight', 'corporate']),
            'requested_date' => now()->addDays(fake()->numberBetween(7, 60))->toDateString(),
            'duration_hours' => fake()->randomElement([4, 6, 8, 24]),
            'start_time' => '10:00:00',
            'guests_adults' => fake()->numberBetween(4, 20),
            'guests_children' => fake()->numberBetween(0, 4),
            'budget_min' => 25000,
            'budget_max' => fake()->randomElement([45000, 80000, 150000]),
            'currency' => 'AED',
            'status' => 'new',
        ];
    }
}
