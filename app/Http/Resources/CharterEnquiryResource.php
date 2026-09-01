<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CharterEnquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CharterEnquiry
 */
class CharterEnquiryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'experience_type' => $this->experience_type,
            'occasion' => $this->occasion,
            'requested_date' => $this->requested_date?->toDateString(),
            'alternative_dates' => $this->alternative_dates ?? [],
            'duration_hours' => $this->duration_hours,
            'start_time' => $this->start_time,
            'guests_adults' => $this->guests_adults,
            'guests_children' => $this->guests_children,
            'guest_count' => $this->guestCount(),
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'currency' => $this->currency,
            'itinerary_notes' => $this->itinerary_notes,
            'requested_extras' => $this->requested_extras ?? [],
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
                'kyc_status' => $this->client->kyc_status,
            ]),
            'marina' => $this->whenLoaded('pickupMarina', fn (): ?array => $this->pickupMarina === null ? null : [
                'id' => $this->pickupMarina->id,
                'name' => $this->pickupMarina->name,
                'timezone' => $this->pickupMarina->timezone,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'matches_count' => $this->whenCounted('matches'),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('charter.enquiries.show', $this->id),
        ];
    }
}
