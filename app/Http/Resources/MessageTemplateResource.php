<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MessageTemplate
 */
class MessageTemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'channel' => $this->channel,
            'subject_en' => $this->subject_en,
            'body_en' => $this->body_en,
            'subject_ar' => $this->subject_ar,
            'body_ar' => $this->body_ar,
            'merge_fields' => $this->merge_fields,
            'category' => $this->category,
            'is_active' => $this->is_active,
            'status' => $this->is_active ? 'active' : 'paused',
            'status_tone' => $this->is_active ? 'success' : 'neutral',
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('engine.message-templates.show', $this->id),
        ];
    }
}
