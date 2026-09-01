<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtInventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtInventoryItem>
 */
class YachtInventoryItemFactory extends Factory
{
    protected $model = YachtInventoryItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['category' => 'Watersports', 'name' => fake()->randomElement(['Seabob', 'Jet ski', 'Paddleboard', 'Snorkelling set']), 'quantity' => 2, 'condition' => 'good'];
    }
}
