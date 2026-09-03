<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Valuation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Valuation
 */
class ValuationResource extends JsonResource
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
            'listing_id' => $this->listing_id,
            'valued_on' => $this->valued_on?->toDateString(),
            'market_low' => $this->market_low,
            'market_high' => $this->market_high,
            'market_spread' => $this->marketSpread(),
            'broker_valuation' => $this->broker_valuation,
            'recommended_asking' => $this->recommended_asking,
            'agreed_asking' => $this->agreed_asking,
            'currency' => $this->currency,
            'comparables' => $this->comparables,
            'rationale' => $this->rationale,
            'pricing_decision' => $this->pricing_decision,
            'adjustment_reason' => $this->adjustment_reason,
            'status' => $this->status,
            'status_tone' => match ($this->pricing_decision) {
                'approved' => 'success',
                'adjusted' => 'attention',
                default => 'neutral',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.valuations.show', $this->id),
        ];
    }
}
