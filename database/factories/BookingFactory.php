<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starts_at' => now()->addDays(14)->setTime(10, 0),
            'ends_at' => now()->addDays(14)->setTime(18, 0),
            'guests_adults' => fake()->numberBetween(6, 16),
            'guests_children' => 0,
            'currency' => 'AED',
            'status' => 'deposit_pending',
        ];
    }
}
