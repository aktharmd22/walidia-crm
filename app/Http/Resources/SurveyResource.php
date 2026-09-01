<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Survey
 */
class SurveyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'listing_id' => $this->listing_id,
            'offer_id' => $this->offer_id,
            'listing' => $this->whenLoaded('listing', fn (): ?string => $this->listing?->reference),
            'type' => $this->type,
            'surveyor_name' => $this->surveyor_name,
            'surveyor_company' => $this->surveyor_company,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cost' => $this->cost,
            'paid_by' => $this->paid_by,
            'outcome' => $this->outcome,
            'findings' => $this->findings,
            'remediation_estimate' => $this->remediation_estimate,
            'status' => $this->status,
            'status_tone' => match ($this->outcome) {
                'clear' => 'success',
                'defects' => 'warning',
                'failed' => 'danger',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.surveys.show', $this->id),
        ];
    }
}
