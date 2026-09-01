<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Call client to confirm guest numbers',
                'Chase signed charter agreement',
                'Upload passport copies for the manifest',
                'Confirm catering order with the vendor',
                'Renew insurance certificate',
            ]),
            'type' => 'next_action',
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
            'due_at' => now()->addDays(fake()->numberBetween(1, 10)),
            'status' => 'open',
            'source' => 'manual',
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => ['due_at' => now()->subDays(3)]);
    }
}
