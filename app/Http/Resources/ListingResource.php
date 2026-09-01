<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Listing
 */
class ListingResource extends JsonResource
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
            'yacht_owner_id' => $this->yacht_owner_id,
            'mandate_type' => $this->mandate_type,
            'asking_price' => $this->asking_price,
            'reserve_price' => $this->when($request->user()?->can('finance.view-amounts') ?? false, fn () => $this->reserve_price),
            'currency' => $this->currency,
            'commission_rate' => $this->commission_rate,
            'agreement_signed_on' => $this->agreement_signed_on?->toDateString(),
            'agreement_expires_on' => $this->agreement_expires_on?->toDateString(),
            'agreement_active' => $this->agreementIsActive(),
            'agreement_expiring' => $this->agreementExpiresWithin(45),
            'requires_nda' => $this->requires_nda,
            'requires_proof_of_funds' => $this->requires_proof_of_funds,
            'is_published' => $this->is_published,
            'listed_on' => $this->listed_on?->toDateString(),
            'marketing_summary' => $this->marketing_summary,
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'active' => 'success',
                'under_offer' => 'attention',
                'sold' => 'info',
                'withdrawn', 'expired' => 'danger',
                default => 'neutral',
            },
            'assigned_user_id' => $this->assigned_user_id,
            'assignee' => $this->whenLoaded('assignee', fn (): ?string => $this->assignee?->name),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.listings.show', $this->id),
        ];
    }
}
