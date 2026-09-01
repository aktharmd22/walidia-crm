<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['disk' => 'private', 'path' => 'attachments/'.fake()->uuid().'.pdf', 'original_name' => 'file.pdf', 'mime' => 'application/pdf', 'size' => 1024];
    }
}
