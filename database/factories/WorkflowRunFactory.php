<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRun>
 */
class WorkflowRunFactory extends Factory
{
    protected $model = WorkflowRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => 'booking',
            'subject_id' => 1,
            'due_at' => now(),
            'status' => 'pending',
        ];
    }
}
