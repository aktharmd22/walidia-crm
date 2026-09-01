<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'safety',
            'name' => 'Safety Equipment Certificate',
            'number' => strtoupper(fake()->bothify('CERT-####')),
            'issued_by' => 'Flag Administration',
            'issued_on' => now()->subYear()->toDateString(),
            'expires_on' => now()->addYear()->toDateString(),
            'blocks_charter' => true,
            'status' => 'valid',
        ];
    }
}
