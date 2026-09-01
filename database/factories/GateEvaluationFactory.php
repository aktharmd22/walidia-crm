<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GateEvaluation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GateEvaluation>
 */
class GateEvaluationFactory extends Factory
{
    protected $model = GateEvaluation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => 'booking', 'subject_id' => 1, 'result' => 'pass', 'evaluated_at' => now(),
        ];
    }
}
