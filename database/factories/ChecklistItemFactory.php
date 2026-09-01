<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    protected $model = ChecklistItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'title' => fake()->sentence(4),
            'status' => 'pending',
            'is_blocking' => false,
        ];
    }

    /** The one the boarding gate reads. */
    public function safetyBriefing(): static
    {
        return $this->state(fn (): array => [
            'key' => 'safety_briefing',
            'title' => 'Safety briefing delivered to all guests',
            'is_blocking' => true,
        ]);
    }
}
