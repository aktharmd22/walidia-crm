<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BuyerRequirement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BuyerRequirement
 */
class BuyerRequirementResource extends JsonResource
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
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'currency' => $this->currency,
            'loa_min' => $this->loa_min,
            'loa_max' => $this->loa_max,
            'year_from' => $this->year_from,
            'preferred_builders' => $this->preferred_builders,
            'regions' => $this->regions,
            'use_case' => $this->use_case,
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => $this->status === 'active' ? 'success' : 'neutral',
            'assigned_user_id' => $this->assigned_user_id,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.buyer-requirements.show', $this->id),
        ];
    }
}
