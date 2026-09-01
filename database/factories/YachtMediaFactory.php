<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\YachtMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YachtMedia>
 */
class YachtMediaFactory extends Factory
{
    protected $model = YachtMedia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['collection' => 'gallery', 'disk' => 'public', 'path' => 'yachts/'.fake()->uuid().'.jpg', 'sort_order' => 0, 'is_public' => true];
    }
}
