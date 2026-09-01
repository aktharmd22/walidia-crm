<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CharterProposal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CharterProposal
 */
class CharterProposalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'version' => $this->version,
            'status' => $this->status,
            'status_tone' => $this->statusTone(),
            'valid_until' => $this->valid_until?->toDateString(),
            'has_expired' => $this->hasExpired(),
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'terms' => $this->terms,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ]),
            'enquiry' => $this->whenLoaded('enquiry', fn (): ?array => $this->enquiry === null ? null : [
                'id' => $this->enquiry->id,
                'reference' => $this->enquiry->reference,
                'url' => route('charter.enquiries.show', $this->enquiry->id),
            ]),
            'items' => $this->whenLoaded('items', fn (): array => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'type' => $item->type,
                'category' => $item->category,
                'description' => $item->description_en,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_treatment' => $item->tax_treatment,
                'tax_amount' => $item->tax_amount,
                'line_total' => $item->line_total,
                'yacht' => $item->yacht?->name,
            ])->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('charter.proposals.show', $this->id),
        ];
    }
}
