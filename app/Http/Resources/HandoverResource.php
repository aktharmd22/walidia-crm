<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Handover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Handover
 */
class HandoverResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'transaction_id' => $this->transaction_id,
            'transaction' => $this->whenLoaded('transaction', fn (): ?string => $this->transaction?->reference),
            'marina_id' => $this->marina_id,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'keys_handed_over' => $this->keys_handed_over,
            'documents_handed_over' => $this->documents_handed_over,
            'inventory_signed' => $this->inventory_signed,
            'flag_registration_updated' => $this->flag_registration_updated,
            'insurance_transferred' => $this->insurance_transferred,
            'is_complete' => $this->isComplete(),
            'outstanding_items' => $this->outstanding_items,
            'status' => $this->status,
            'status_tone' => $this->isComplete() ? 'success' : 'warning',
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.handovers.show', $this->id),
        ];
    }
}
