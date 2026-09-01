<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterFeedback>
 */
class CharterFeedbackFactory extends Factory
{
    protected $model = CharterFeedback::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sent_at' => now(), 'nps' => fake()->numberBetween(7, 10),
        ];
    }
}
