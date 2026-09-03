<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MaintenanceSchedule
 */
class MaintenanceScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'yacht_id' => $this->yacht_id,
            'yacht' => $this->whenLoaded('yacht', fn (): ?string => $this->yacht?->name),
            'vendor_id' => $this->vendor_id,
            'system' => $this->system,
            'title' => $this->title,
            'description' => $this->description,
            'interval_days' => $this->interval_days,
            'interval_engine_hours' => $this->interval_engine_hours,
            'last_done_on' => $this->last_done_on?->toDateString(),
            'next_due_on' => $this->next_due_on?->toDateString(),
            'is_due' => $this->isDue(),
            'budget_cost' => $this->budget_cost,
            'blocks_charter' => $this->blocks_charter,
            'is_active' => $this->is_active,
            'status' => $this->is_active ? 'active' : 'paused',
            'status_tone' => $this->isDue() ? 'danger' : ($this->is_active ? 'success' : 'neutral'),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('management.maintenance-schedules.show', $this->id),
        ];
    }
}
