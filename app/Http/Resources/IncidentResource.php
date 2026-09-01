<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Incident
 */
class IncidentResource extends JsonResource
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
            'insurance_claim_ref' => $this->insurance_claim_ref,
            'type' => $this->type,
            'severity' => $this->severity,
            'severity_tone' => match ($this->severity) {
                'critical', 'major' => 'danger',
                'moderate' => 'attention',
                default => 'warning',
            },
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'description' => $this->description,
            'immediate_action' => $this->immediate_action,
            'injuries' => $this->injuries,
            'authorities_notified' => $this->authorities_notified,
            'status' => $this->status,
            'status_tone' => $this->status === 'closed' ? 'success' : 'danger',
            'closed_at' => $this->closed_at?->toIso8601String(),
            'booking' => $this->whenLoaded('booking', fn (): ?array => $this->booking === null ? null : [
                'id' => $this->booking->id,
                'reference' => $this->booking->reference,
                'url' => route('charter.bookings.show', $this->booking->id),
            ]),
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('charter.incidents.show', $this->id),
        ];
    }
}
