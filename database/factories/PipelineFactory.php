<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pipeline>
 */
class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['key' => fake()->unique()->randomElement(['charter', 'buyer', 'seller']), 'name' => 'Pipeline', 'is_active' => true];
    }
}
