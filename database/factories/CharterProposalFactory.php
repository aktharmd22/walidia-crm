<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CharterProposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharterProposal>
 */
class CharterProposalFactory extends Factory
{
    protected $model = CharterProposal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'version' => 1,
            'valid_until' => now()->addDays(7)->toDateString(),
            'currency' => 'AED',
            'subtotal' => 40000,
            'tax_amount' => 2000,
            'total' => 42000,
            'status' => 'draft',
        ];
    }
}
