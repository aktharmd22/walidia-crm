<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payout
 */
class PayoutResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'transaction_id' => $this->transaction_id,
            'booking_id' => $this->booking_id,
            'deal_id' => $this->deal_id,
            'type' => $this->type,
            'payee_name' => $this->payee_name,
            'payee_client_id' => $this->payee_client_id,
            'payee_vendor_id' => $this->payee_vendor_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'amount_aed' => $this->amount_aed,
            'method' => $this->method,
            'bank_reference' => $this->bank_reference,
            'due_on' => $this->due_on?->toDateString(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'is_paid' => $this->isPaid(),
            'is_overdue' => $this->isOverdue(),
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => match (true) {
                $this->isPaid() => 'success',
                $this->isOverdue() => 'danger',
                $this->status === 'approved' => 'attention',
                $this->status === 'cancelled' => 'neutral',
                default => 'warning',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('finance.payouts.show', $this->id),
        ];
    }
}
