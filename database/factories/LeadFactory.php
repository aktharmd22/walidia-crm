<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => '+9715'.fake()->numberBetween(10000000, 99999999),
            'business_line' => fake()->randomElement(['charter', 'brokerage', 'management']),
            'message' => fake()->randomElement([
                'Looking for a 40m for a family day charter from Yas Marina, 12 guests.',
                'Interested in a sunset cruise for a corporate group of 20.',
                'Would like to view yachts in the 35-45m range, budget around AED 30M.',
                'Enquiring about management for a 42m currently berthed in Dubai Harbour.',
            ]),
            'status' => 'new',
            'sla_due_at' => now()->addHours(4),
        ];
    }

    public function contacted(): static
    {
        return $this->state(fn (): array => [
            'status' => 'contacted',
            'first_response_at' => now()->subHours(2),
        ]);
    }
}
