<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChecklistTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTemplateItem>
 */
class ChecklistTemplateItemFactory extends Factory
{
    protected $model = ChecklistTemplateItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'section' => 'planning',
            'title_en' => fake()->sentence(4),
            'responsible_role' => 'operations',
            'offset_hours' => 0,
            'requires_photo' => false,
            'requires_signature' => false,
            'is_blocking' => false,
            'sort_order' => 10,
        ];
    }
}
