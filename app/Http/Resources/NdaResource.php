<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Nda;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Nda
 */
class NdaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'listing_id' => $this->listing_id,
            'listing' => $this->whenLoaded('listing', fn (): ?string => $this->listing?->reference),
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn (): ?string => $this->client?->full_name),
            'scope' => $this->scope,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'signed_at' => $this->signed_at?->toIso8601String(),
            'expires_on' => $this->expires_on?->toDateString(),
            'is_signed' => $this->isSigned(),
            'status' => $this->status,
            'status_tone' => $this->isSigned() ? 'success' : ($this->status === 'sent' ? 'warning' : 'neutral'),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.ndas.show', $this->id),
        ];
    }
}
