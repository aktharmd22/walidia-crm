<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OperationsChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationsChecklist>
 */
class OperationsChecklistFactory extends Factory
{
    protected $model = OperationsChecklist::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 'open',
            'completion_pct' => 0,
            'started_at' => now(),
        ];
    }
}
