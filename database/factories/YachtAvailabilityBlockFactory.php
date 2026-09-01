<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtAvailabilityBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtAvailabilityBlock>
 */
class YachtAvailabilityBlockFactory extends Factory
{
    protected $model = YachtAvailabilityBlock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(4), 'type' => 'booking'];
    }
}
