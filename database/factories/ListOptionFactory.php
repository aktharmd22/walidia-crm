<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ListOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListOption>
 */
class ListOptionFactory extends Factory
{
    protected $model = ListOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['list_key' => 'experience_type', 'value' => fake()->unique()->word(), 'label_en' => fake()->word(), 'sort_order' => 0, 'is_active' => true];
    }
}
