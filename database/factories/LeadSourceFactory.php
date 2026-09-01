<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadSource>
 */
class LeadSourceFactory extends Factory
{
    protected $model = LeadSource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['name' => fake()->unique()->randomElement(['Website', 'WhatsApp', 'Referral', 'Instagram', 'Walk-in', 'Partner agent']), 'channel' => 'direct', 'is_active' => true];
    }
}
