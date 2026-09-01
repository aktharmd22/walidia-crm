<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SignedLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SignedLink>
 */
class SignedLinkFactory extends Factory
{
    protected $model = SignedLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['token_hash' => hash('sha256', fake()->uuid()), 'purpose' => 'signature', 'subject_type' => 'document', 'subject_id' => 1, 'expires_at' => now()->addDays(7)];
    }
}
