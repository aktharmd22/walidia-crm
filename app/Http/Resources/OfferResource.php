<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Offer
 */
class OfferResource extends JsonResource
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'deposit_amount' => $this->deposit_amount,
            'subject_to_survey' => $this->subject_to_survey,
            'subject_to_sea_trial' => $this->subject_to_sea_trial,
            'proof_of_funds_received' => $this->proof_of_funds_received,
            'valid_until' => $this->valid_until?->toDateString(),
            'conditions' => $this->conditions,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'response_notes' => $this->response_notes,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'accepted' => 'success',
                'submitted', 'countered' => 'attention',
                'rejected', 'withdrawn', 'lapsed' => 'danger',
                default => 'neutral',
            },
            'assigned_user_id' => $this->assigned_user_id,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.offers.show', $this->id),
        ];
    }
}
