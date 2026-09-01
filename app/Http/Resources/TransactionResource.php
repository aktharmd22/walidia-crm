<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
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
            'offer_id' => $this->offer_id,
            'listing' => $this->whenLoaded('listing', fn (): ?string => $this->listing?->reference),
            'buyer_client_id' => $this->buyer_client_id,
            'buyer' => $this->whenLoaded('buyer', fn (): ?string => $this->buyer?->full_name),
            'seller_owner_id' => $this->seller_owner_id,
            'agreed_price' => $this->agreed_price,
            'currency' => $this->currency,
            'deposit_amount' => $this->deposit_amount,
            'deposit_cleared_at' => $this->deposit_cleared_at?->toIso8601String(),
            'balance_amount' => $this->balance_amount,
            'balance_cleared_at' => $this->balance_cleared_at?->toIso8601String(),
            'funds_cleared' => $this->fundsCleared(),
            'escrow_agent' => $this->escrow_agent,
            'contract_type' => $this->contract_type,
            'contract_signed_on' => $this->contract_signed_on?->toDateString(),
            'expected_closing_on' => $this->expected_closing_on?->toDateString(),
            'aml_cleared' => $this->aml_cleared,
            'aml_cleared_at' => $this->aml_cleared_at?->toIso8601String(),
            'ownership_transferred_at' => $this->ownership_transferred_at?->toIso8601String(),
            'is_transferred' => $this->isTransferred(),
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'completed' => 'success',
                'transferring' => 'attention',
                'aborted' => 'danger',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.transactions.show', $this->id),
        ];
    }
}
