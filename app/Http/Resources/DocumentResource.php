<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'original_name' => $this->original_name,
            'mime' => $this->mime,
            'size' => $this->size,
            'size_label' => $this->size > 0 ? round($this->size / 1024 / 1024, 2).' MB' : null,
            'version' => $this->version,
            'issued_on' => $this->issued_on?->toDateString(),
            'expires_on' => $this->expires_on?->toDateString(),
            'is_expired' => $this->isExpired(),
            'is_expiring' => $this->isExpiring(),
            'expiry_tone' => match (true) {
                $this->isExpired() => 'danger',
                $this->isExpiring() => 'warning',
                default => 'success',
            },
            'visibility' => $this->visibility,
            'is_sensitive' => $this->is_sensitive,
            'requires_signature' => $this->requires_signature,
            'signed_at' => $this->signed_at?->toIso8601String(),
            'status' => $this->status,
            'subject' => $this->when($this->subject_type !== null, fn (): array => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
            ]),
            'uploader' => $this->whenLoaded('uploader', fn (): ?string => $this->uploader?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            // Never a direct storage path: downloads go through a policy check
            // and a five-minute signed URL (D-015).
            'download_url' => route('documents.download', $this->id),
            'url' => route('documents.show', $this->id),
        ];
    }
}
