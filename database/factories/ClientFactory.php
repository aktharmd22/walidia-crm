<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Realistic Walidia clients: Gulf and international names, UAE mobile formats,
 * and the mix of types one record can hold at once.
 *
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /** @var list<string> */
    private const FIRST_NAMES = [
        'Khalid', 'Aisha', 'Omar', 'Fatima', 'Yousef', 'Maryam', 'Saeed', 'Noura',
        'James', 'Charlotte', 'Alexander', 'Sofia', 'Dmitri', 'Elena', 'Rajesh', 'Priya',
    ];

    /** @var list<string> */
    private const LAST_NAMES = [
        'Al Mansouri', 'Al Zaabi', 'Al Suwaidi', 'Al Hashimi', 'Al Marri', 'Al Kaabi',
        'Whitmore', 'Beaumont', 'Petrov', 'Novak', 'Shah', 'Iyer', 'Lindqvist', 'Moreau',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->randomElement(self::FIRST_NAMES);
        $last = fake()->randomElement(self::LAST_NAMES);

        return [
            'first_name' => $first,
            'last_name' => $last,
            'full_name' => "{$first} {$last}",
            'client_type' => [fake()->randomElement(['charter_guest', 'buyer', 'seller', 'owner'])],
            'email' => fake()->unique()->safeEmail(),
            'mobile' => '+9715'.fake()->numberBetween(10000000, 99999999),
            'preferred_channel' => fake()->randomElement(['whatsapp', 'email', 'phone', 'agent']),
            'nationality' => fake()->randomElement(['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'United Kingdom', 'India', 'Russia']),
            'country' => 'United Arab Emirates',
            'city' => fake()->randomElement(['Abu Dhabi', 'Dubai', 'Sharjah']),
            'emirate' => fake()->randomElement(['Abu Dhabi', 'Dubai']),
            'vip_level' => 'none',
            'status' => 'active',
            'kyc_status' => 'not_started',
        ];
    }

    public function vip(): static
    {
        return $this->state(fn (): array => [
            'vip_level' => fake()->randomElement(['vip', 'uhnw', 'protected']),
            'passport_number' => strtoupper(fake()->bothify('??######')),
            'emirates_id' => fake()->numerify('784-####-#######-#'),
            'dietary_preferences' => fake()->randomElement(['Pescatarian', 'Halal only', 'No shellfish', 'Vegan']),
            'allergies' => fake()->randomElement(['Peanuts', 'Shellfish', 'None recorded', 'Latex']),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'kyc_status' => 'verified',
            'kyc_verified_at' => now()->subDays(fake()->numberBetween(1, 200)),
            'kyc_expires_on' => now()->addYear(),
        ]);
    }

    public function owner(): static
    {
        return $this->state(fn (): array => ['client_type' => ['owner']]);
    }
}
