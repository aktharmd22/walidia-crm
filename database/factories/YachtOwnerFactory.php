<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtOwner>
 */
class YachtOwnerFactory extends Factory
{
    protected $model = YachtOwner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['ownership_percentage' => 100, 'is_primary' => true];
    }
}
