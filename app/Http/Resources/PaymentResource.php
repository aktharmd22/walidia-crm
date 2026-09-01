<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'method' => $this->method,
            'gateway_reference' => $this->gateway_reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'amount_aed' => $this->amount_aed,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'is_cleared' => $this->isCleared(),
            'received_at' => $this->received_at?->toIso8601String(),
            'cleared_at' => $this->cleared_at?->toIso8601String(),
            'reconciled_at' => $this->reconciled_at?->toIso8601String(),
            'unallocated' => $this->unallocatedAmount(),
            'bank_charge_amount' => $this->bank_charge_amount,
            'notes' => $this->notes,
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ]),
            'allocations' => $this->whenLoaded('allocations', fn (): array => $this->allocations->map(fn ($allocation): array => [
                'id' => $allocation->id,
                'amount' => $allocation->amount,
                'invoice' => $allocation->invoice?->reference,
                'invoice_id' => $allocation->invoice_id,
            ])->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('finance.payments.show', $this->id),
        ];
    }
}
