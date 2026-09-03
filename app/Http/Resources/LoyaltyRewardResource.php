<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LoyaltyReward;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoyaltyReward
 */
class LoyaltyRewardResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'type' => $this->type,
            'value' => $this->value,
            'currency' => $this->currency,
            'points' => $this->points,
            'code' => $this->code,
            'description' => $this->description,
            'valid_from' => $this->valid_from?->toDateString(),
            'expires_on' => $this->expires_on?->toDateString(),
            'redeemed_at' => $this->redeemed_at?->toIso8601String(),
            'is_redeemable' => $this->isRedeemable(),
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'redeemed' => 'success',
                'expired', 'cancelled' => 'neutral',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('crm.rewards.show', $this->id),
        ];
    }
}
