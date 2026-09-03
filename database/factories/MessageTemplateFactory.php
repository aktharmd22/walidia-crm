<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageTemplate>
 */
class MessageTemplateFactory extends Factory
{
    protected $model = MessageTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->sentence(3),
            'channel' => 'email',
            'subject_en' => 'Your charter with Walidia',
            'body_en' => 'Dear {{client_name}}, your charter on {{yacht_name}} is confirmed.',
            'category' => 'client',
            'is_active' => true,
        ];
    }
}
