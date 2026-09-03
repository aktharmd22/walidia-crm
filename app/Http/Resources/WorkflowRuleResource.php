<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WorkflowRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkflowRule
 */
class WorkflowRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'business_line' => $this->business_line,
            'trigger_type' => $this->trigger_type,
            'trigger_event' => $this->trigger_event,
            'anchor_field' => $this->anchor_field,
            'offset_hours' => $this->offset_hours,
            'action' => $this->action,
            'audience' => $this->audience,
            'message_template_id' => $this->message_template_id,
            'template' => $this->whenLoaded('template', fn (): ?string => $this->template?->name),
            'conditions' => $this->conditions,
            'is_active' => $this->is_active,
            'status' => $this->is_active ? 'active' : 'paused',
            'status_tone' => $this->is_active ? 'success' : 'neutral',
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('engine.workflows.show', $this->id),
        ];
    }
}
