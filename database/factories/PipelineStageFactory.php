<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['key' => fake()->unique()->word(), 'name' => fake()->word(), 'sort_order' => 0, 'colour_token' => 'neutral', 'probability' => 20];
    }
}
