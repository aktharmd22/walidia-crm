<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SecurityDeposit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SecurityDeposit
 */
class SecurityDepositResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'is_held' => $this->isHeld(),
            'collected_at' => $this->collected_at?->toIso8601String(),
            'released_amount' => $this->released_amount,
            'released_at' => $this->released_at?->toIso8601String(),
            'deduction_reason' => $this->deduction_reason,
            'booking' => $this->whenLoaded('booking', fn (): ?array => $this->booking === null ? null : [
                'id' => $this->booking->id,
                'reference' => $this->booking->reference,
                'client' => $this->booking->client?->full_name,
                'url' => route('charter.bookings.show', $this->booking->id),
            ]),
            'url' => route('finance.security-deposits.show', $this->id),
        ];
    }
}
