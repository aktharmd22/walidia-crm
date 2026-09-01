<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Berth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Berth>
 */
class BerthFactory extends Factory
{
    protected $model = Berth::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['code' => 'B-'.fake()->numberBetween(1, 99), 'max_loa_m' => 60, 'is_active' => true];
    }
}
