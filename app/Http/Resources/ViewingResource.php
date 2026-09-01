<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Viewing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Viewing
 */
class ViewingResource extends JsonResource
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
            'listing' => $this->whenLoaded('listing', fn (): ?string => $this->listing?->reference),
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn (): ?string => $this->client?->full_name),
            'marina_id' => $this->marina_id,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'attendees' => $this->attendees,
            'feedback' => $this->feedback,
            'interest_level' => $this->interest_level,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'scheduled' => 'info',
                'completed' => 'success',
                'cancelled', 'no_show' => 'danger',
                default => 'warning',
            },
            'assigned_user_id' => $this->assigned_user_id,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('brokerage.viewings.show', $this->id),
        ];
    }
}
