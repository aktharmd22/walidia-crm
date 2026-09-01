<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SignatureRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SignatureRequest>
 */
class SignatureRequestFactory extends Factory
{
    protected $model = SignatureRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['provider' => 'internal', 'signer_name' => fake()->name(), 'signer_email' => fake()->safeEmail(), 'status' => 'draft'];
    }
}
