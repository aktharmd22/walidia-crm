<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Crew;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Crew
 */
class CrewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'home_marina_id' => $this->home_marina_id,
            'notes' => $this->notes,
            'full_name' => $this->full_name,
            'role' => $this->role,
            'employment_type' => $this->employment_type,
            'nationality' => $this->nationality,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'day_rate' => $this->when($request->user()?->can('finance.view-amounts') ?? false, fn () => $this->day_rate),
            'currency' => $this->currency,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'active' => 'success',
                'on_leave' => 'warning',
                default => 'neutral',
            },
            'has_expired_documents' => $this->hasExpiredDocuments(),
            'expiring_soon' => $this->documentsExpiringWithin(30),
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('crew.show', $this->id),
        ];
    }
}
