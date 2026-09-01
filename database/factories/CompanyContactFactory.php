<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CompanyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyContact>
 */
class CompanyContactFactory extends Factory
{
    protected $model = CompanyContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['name' => fake()->name(), 'position' => 'Director', 'email' => fake()->safeEmail(), 'is_primary' => false];
    }
}
