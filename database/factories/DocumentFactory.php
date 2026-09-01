<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Charter agreement', 'Passport copy', 'Insurance certificate', 'Trade licence', 'Survey report']),
            'category' => fake()->randomElement(['contract', 'kyc', 'certificate', 'survey']),
            'disk' => 'private',
            'path' => 'documents/general/0/'.now()->format('Y/m').'/'.fake()->uuid().'.pdf',
            'original_name' => 'document.pdf',
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(50000, 4000000),
            'version' => 1,
            'visibility' => 'internal',
            'status' => 'active',
        ];
    }

    public function expiring(int $days = 20): static
    {
        return $this->state(fn (): array => ['expires_on' => now()->addDays($days)]);
    }
}
