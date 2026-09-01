<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Marina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Marina>
 */
class MarinaFactory extends Factory
{
    protected $model = Marina::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Yas Marina', 'Al Bateen Marina', 'Zayed Port Marina', 'Dubai Harbour',
                'Port Rashid Marina', 'Dubai Marina Yacht Club', 'Lusail Marina',
                'Marina Bandar Al Rowdha', 'Eden Island Marina', 'Hulhumale Marina',
            ]),
            'country' => 'United Arab Emirates',
            'emirate' => fake()->randomElement(['Abu Dhabi', 'Dubai']),
            'city' => fake()->randomElement(['Abu Dhabi', 'Dubai']),
            'timezone' => 'Asia/Dubai',
            'requires_manifest' => fake()->boolean(60),
            'manifest_format' => 'pdf',
            'is_active' => true,
        ];
    }
}
