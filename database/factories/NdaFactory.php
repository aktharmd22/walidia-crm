<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Nda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nda>
 */
class NdaFactory extends Factory
{
    protected $model = Nda::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scope' => 'listing',
            'sent_at' => now()->subDays(3),
            'signed_at' => now()->subDays(2),
            'expires_on' => now()->addYear()->toDateString(),
            'status' => 'signed',
        ];
    }
}
