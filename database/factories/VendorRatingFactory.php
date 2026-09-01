<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VendorRating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorRating>
 */
class VendorRatingFactory extends Factory
{
    protected $model = VendorRating::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score' => fake()->numberBetween(3, 5), 'punctuality' => 4, 'quality' => 4, 'value' => 4,
        ];
    }
}
