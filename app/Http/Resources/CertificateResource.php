<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Certificate
 */
class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'yacht_id' => $this->yacht_id,
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'type' => $this->type,
            'name' => $this->name,
            'number' => $this->number,
            'issued_by' => $this->issued_by,
            'issued_on' => $this->issued_on?->toDateString(),
            'expires_on' => $this->expires_on?->toDateString(),
            'blocks_charter' => $this->blocks_charter,
            'is_expired' => $this->isExpired(),
            'is_expiring' => $this->isExpiring(),
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => $this->isExpired() ? 'danger' : ($this->isExpiring() ? 'warning' : 'success'),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('management.certificates.show', $this->id),
        ];
    }
}
