<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inspection
 */
class InspectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'yacht_id' => $this->yacht_id,
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'listing_id' => $this->listing_id,
            'handover_id' => $this->handover_id,
            'type' => $this->type,
            'inspected_at' => $this->inspected_at?->toIso8601String(),
            'hull_condition' => $this->hull_condition,
            'engine_condition' => $this->engine_condition,
            'interior_condition' => $this->interior_condition,
            'systems_condition' => $this->systems_condition,
            'findings' => $this->findings,
            'recommended_works' => $this->recommended_works,
            'estimated_works_cost' => $this->estimated_works_cost,
            'outcome' => $this->outcome,
            'is_clear' => $this->isClear(),
            'status' => $this->status,
            'status_tone' => match ($this->outcome) {
                'clear' => 'success',
                'defects' => 'warning',
                'failed' => 'danger',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.inspections.show', $this->id),
        ];
    }
}
