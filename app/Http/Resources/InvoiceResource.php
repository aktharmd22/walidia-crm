<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'is_issued' => $this->isIssued(),
            'is_editable' => $this->isEditable(),
            'is_overdue' => $this->isOverdue(),
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'place_of_supply' => $this->place_of_supply,
            'tax_treatment' => $this->tax_treatment,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'amount_paid' => $this->amount_paid,
            'amount_due' => $this->amount_due,
            'supplier_trn' => $this->supplier_trn,
            'notes' => $this->notes,
            'void_reason' => $this->void_reason,
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ]),
            'items' => $this->whenLoaded('items', fn (): array => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'description' => $item->description_en,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_treatment' => $item->tax_treatment,
                'tax_amount' => $item->tax_amount,
                'line_total' => $item->line_total,
            ])->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('finance.invoices.show', $this->id),
        ];
    }
}
