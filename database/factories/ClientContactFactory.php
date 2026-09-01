<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClientContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientContact>
 */
class ClientContactFactory extends Factory
{
    protected $model = ClientContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['name' => fake()->name(), 'role' => 'Personal assistant', 'email' => fake()->safeEmail(), 'mobile' => '+9715'.fake()->numberBetween(10000000, 99999999), 'is_primary' => false];
    }
}
