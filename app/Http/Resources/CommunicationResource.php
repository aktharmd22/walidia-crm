<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Communication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Communication
 */
class CommunicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn (): ?string => $this->client?->full_name),
            'channel' => $this->channel,
            'direction' => $this->direction,
            'to_address' => $this->to_address,
            'subject' => $this->subject,
            'body' => $this->body,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'failure_reason' => $this->failure_reason,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'url' => route('engine.communications.show', $this->id),
        ];
    }
}
