<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostSheet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CostSheet
 */
class CostSheetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'currency' => $this->currency,
            'total_offer' => $this->total_offer,
            'total_cost' => $this->total_cost,
            'total_profit' => $this->total_profit,
            'margin_pct' => $this->margin_pct,
            'effective_phase' => $this->effectivePhase(),
            'is_closed' => $this->isClosed(),
            'writable_phases' => $request->user() === null ? [] : $this->writablePhasesFor($request->user()),
            'booking' => $this->whenLoaded('booking', fn (): ?array => $this->booking === null ? null : [
                'id' => $this->booking->id,
                'reference' => $this->booking->reference,
                'url' => route('charter.bookings.show', $this->booking->id),
            ]),
            'lines' => $this->whenLoaded('lines', fn (): array => $this->lines->map(fn ($line): array => [
                'id' => $line->id,
                'phase' => $line->phase,
                'section' => $line->section,
                'category' => $line->category,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'amount' => $line->amount,
                'tax_treatment' => $line->tax_treatment,
                'tax_amount' => $line->tax_amount,
            ])->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('charter.cost-sheets.show', $this->id),
        ];
    }
}
