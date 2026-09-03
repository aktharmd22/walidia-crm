<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClientJourney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientJourney>
 */
class ClientJourneyFactory extends Factory
{
    protected $model = ClientJourney::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'post_charter',
            'status' => 'open',
        ];
    }
}
