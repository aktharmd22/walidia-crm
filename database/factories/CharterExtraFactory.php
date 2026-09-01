<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterExtra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterExtra>
 */
class CharterExtraFactory extends Factory
{
    protected $model = CharterExtra::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => 'guest_request',
            'description' => fake()->randomElement(['Extra hour', 'Additional watersports', 'Champagne', 'Late return']),
            'quantity' => 1,
            'unit_price' => 3500,
            'amount' => 3500,
            'status' => 'requested',
        ];
    }
}
