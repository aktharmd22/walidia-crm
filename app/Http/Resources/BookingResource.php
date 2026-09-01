<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canSeeAmounts = $request->user()?->can('finance.view-amounts') ?? false;

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'status_label' => str_replace('_', ' ', $this->status),
            'status_tone' => $this->statusTone(),

            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            // The marina's own clock, because a charter out of Malé does not
            // leave on Dubai time (D-010).
            'starts_local' => $this->departureLocal()->format('Y-m-d H:i'),
            'timezone' => $this->departureMarina?->timezone ?? config('walidia.display_timezone'),
            'duration_hours' => $this->durationHours(),

            'guests_adults' => $this->guests_adults,
            'guests_children' => $this->guests_children,
            'guest_count' => $this->guestCount(),

            'itinerary' => $this->itinerary,
            'special_requests' => $this->special_requests,
            'currency' => $this->currency,
            'apa_amount' => $this->when($canSeeAmounts, fn () => $this->apa_amount),

            'contract_signed_at' => $this->contract_signed_at?->toIso8601String(),
            'operational_release_at' => $this->operational_release_at?->toIso8601String(),
            'is_released' => $this->isReleased(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'cancellation_fee' => $this->when($canSeeAmounts, fn () => $this->cancellation_fee),

            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
                'kyc_status' => $this->client->kyc_status,
            ]),
            'yacht' => $this->whenLoaded('yacht', fn (): ?array => $this->yacht === null ? null : [
                'id' => $this->yacht->id,
                'name' => $this->yacht->name,
            ]),
            'marina' => $this->whenLoaded('departureMarina', fn (): ?array => $this->departureMarina === null ? null : [
                'id' => $this->departureMarina->id,
                'name' => $this->departureMarina->name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),

            'value' => $this->when(
                $canSeeAmounts && $this->relationLoaded('costSheet'),
                fn () => $this->costSheet?->total_offer,
            ),

            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('charter.bookings.show', $this->id),
        ];
    }
}
