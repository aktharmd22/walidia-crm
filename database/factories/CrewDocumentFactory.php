<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrewDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrewDocument>
 */
class CrewDocumentFactory extends Factory
{
    protected $model = CrewDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['visa', 'seaman_book', 'stcw', 'medical', 'licence']),
            'issued_on' => now()->subYear()->toDateString(),
            'expires_on' => now()->addYear()->toDateString(),
            'status' => 'valid',
        ];
    }
}
