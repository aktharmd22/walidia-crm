<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Handover;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Handover>
 */
class HandoverFactory extends Factory
{
    protected $model = Handover::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scheduled_at' => now()->addMonth(),
            'status' => 'pending',
        ];
    }
}
