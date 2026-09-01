<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OwnerStatement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OwnerStatement
 */
class OwnerStatementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'management_agreement_id' => $this->management_agreement_id,
            'yacht_id' => $this->yacht_id,
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'charter_revenue' => $this->charter_revenue,
            'management_fee' => $this->management_fee,
            'operating_costs' => $this->operating_costs,
            'maintenance_costs' => $this->maintenance_costs,
            'crew_costs' => $this->crew_costs,
            'net_to_owner' => $this->net_to_owner,
            'currency' => $this->currency,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'paid' => 'success',
                'approved' => 'info',
                'issued' => 'attention',
                default => 'neutral',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('management.owner-statements.show', $this->id),
        ];
    }
}
