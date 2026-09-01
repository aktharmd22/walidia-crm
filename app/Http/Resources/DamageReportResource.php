<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DamageReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DamageReport
 */
class DamageReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'booking_id' => $this->booking_id,
            'yacht_id' => $this->yacht_id,
            'discovered_at' => $this->discovered_at?->toIso8601String(),
            'description' => $this->description,
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'deduct_from_deposit' => $this->deduct_from_deposit,
            'inspection_status' => $this->inspection_status,
            'status_tone' => $this->isClosed() ? 'success' : 'warning',
            'is_closed' => $this->isClosed(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'resolution' => $this->resolution,
            'booking' => $this->whenLoaded('booking', fn (): ?array => $this->booking === null ? null : [
                'id' => $this->booking->id,
                'reference' => $this->booking->reference,
                'url' => route('charter.bookings.show', $this->booking->id),
            ]),
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('charter.damage-reports.show', $this->id),
        ];
    }
}
