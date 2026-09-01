<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GuestManifest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestManifest>
 */
class GuestManifestFactory extends Factory
{
    protected $model = GuestManifest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'format' => 'pdf', 'status' => 'draft', 'generated_at' => now(),
        ];
    }
}
