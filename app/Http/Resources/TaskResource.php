<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            'priority_tone' => match ($this->priority) {
                'urgent' => 'danger',
                'high' => 'attention',
                'low' => 'neutral',
                default => 'info',
            },
            'status' => $this->status,
            'status_tone' => match (true) {
                $this->status === 'done' => 'success',
                $this->isOverdue() => 'danger',
                default => 'info',
            },
            'due_at' => $this->due_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'source' => $this->source,
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'subject' => $this->when($this->subject_type !== null, fn (): array => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
                'label' => $this->subjectLabel(),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('tasks.show', $this->id),
        ];
    }
}
