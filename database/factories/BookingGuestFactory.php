<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BookingGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingGuest>
 */
class BookingGuestFactory extends Factory
{
    protected $model = BookingGuest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nationality' => fake()->randomElement(['United Arab Emirates', 'United Kingdom', 'India']),
            'document_type' => 'passport',
            'document_number' => strtoupper(fake()->bothify('??######')),
            'is_lead_guest' => false,
            'id_verified' => false,
        ];
    }
}
