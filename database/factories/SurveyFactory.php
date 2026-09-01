<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'condition',
            'surveyor_name' => fake()->name(),
            'surveyor_company' => fake()->company(),
            'scheduled_at' => now()->addDays(10),
            'cost' => 18000,
            'paid_by' => 'buyer',
            'status' => 'scheduled',
        ];
    }
}
