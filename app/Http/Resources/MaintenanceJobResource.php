<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MaintenanceJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MaintenanceJob
 */
class MaintenanceJobResource extends JsonResource
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
            'management_agreement_id' => $this->management_agreement_id,
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->whenLoaded('vendor', fn (): ?string => $this->vendor?->displayName()),
            'assigned_user_id' => $this->assigned_user_id,
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_on' => $this->due_on?->toDateString(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'currency' => $this->currency,
            'owner_approval_required' => $this->owner_approval_required,
            'owner_approved_at' => $this->owner_approved_at?->toIso8601String(),
            'blocks_charter' => $this->blocks_charter,
            'is_overdue' => $this->isOverdue(),
            'status' => $this->status,
            'status_tone' => match (true) {
                $this->status === 'done' => 'success',
                $this->isOverdue() => 'danger',
                $this->priority === 'critical' => 'danger',
                $this->status === 'in_progress' => 'attention',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('management.maintenance.show', $this->id),
        ];
    }
}
