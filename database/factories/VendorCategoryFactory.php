<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VendorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorCategory>
 */
class VendorCategoryFactory extends Factory
{
    protected $model = VendorCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Catering', 'Watersports', 'Transfers', 'Flowers', 'Photography', 'Fuel', 'Technical', 'Cleaning']),
        ];
    }
}
