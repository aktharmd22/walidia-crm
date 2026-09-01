<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Viewing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Viewing>
 */
class ViewingFactory extends Factory
{
    protected $model = Viewing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scheduled_at' => now()->addWeek()->setTime(11, 0),
            'duration_minutes' => 90,
            'status' => 'requested',
        ];
    }
}
