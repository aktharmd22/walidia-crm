<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkflowRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowRule>
 */
class WorkflowRuleFactory extends Factory
{
    protected $model = WorkflowRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'name' => fake()->sentence(3),
            'business_line' => 'charter',
            'trigger_type' => 'event',
            'trigger_event' => 'booking.confirmed',
            'offset_hours' => 0,
            'action' => 'send_message',
            'audience' => 'client',
            'is_active' => true,
        ];
    }
}
