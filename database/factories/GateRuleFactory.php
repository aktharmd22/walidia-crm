<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GateRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GateRule>
 */
class GateRuleFactory extends Factory
{
    protected $model = GateRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'name_en' => fake()->words(3, true),
            'subject_type' => 'booking',
            'trigger_type' => 'action',
            'action_key' => 'bookings.release-operations',
            'severity' => 'hard',
            'conditions' => [['check' => 'payment.deposit_cleared', 'params' => []]],
            'block_message_en' => 'Blocked.',
            'is_active' => true,
        ];
    }
}
