<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SavedView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedView>
 */
class SavedViewFactory extends Factory
{
    protected $model = SavedView::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module' => 'bookings',
            'name' => fake()->words(2, true),
            'filters' => ['status' => 'confirmed'],
            'columns' => ['yacht', 'client', 'status'],
            'is_shared' => false,
            'is_default' => false,
        ];
    }
}
