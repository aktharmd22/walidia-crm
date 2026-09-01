<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    protected $model = DocumentVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['version' => 1, 'path' => 'documents/'.fake()->uuid().'.pdf', 'size' => 2048];
    }
}
