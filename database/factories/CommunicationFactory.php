<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Communication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Communication>
 */
class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel' => 'email',
            'direction' => 'outbound',
            'to_address' => fake()->safeEmail(),
            'subject' => 'Your charter with Walidia',
            'body' => fake()->paragraph(),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
