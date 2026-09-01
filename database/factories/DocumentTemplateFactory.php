<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    protected $model = DocumentTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(2), 'name' => fake()->words(3, true), 'type' => 'contract', 'is_active' => true];
    }
}
