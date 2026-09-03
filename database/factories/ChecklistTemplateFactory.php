<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTemplate>
 */
class ChecklistTemplateFactory extends Factory
{
    protected $model = ChecklistTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Charter operations — '.fake()->word(),
            'business_line' => 'charter',
            'trigger' => 'booking.confirmed',
            'is_active' => true,
        ];
    }
}
