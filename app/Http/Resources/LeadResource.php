<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'message' => $this->message,
            'business_line' => $this->business_line,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'new' => 'info',
                'contacted' => 'warning',
                'qualified', 'registered' => 'success',
                'unqualified' => 'neutral',
                'duplicate' => 'danger',
                default => 'neutral',
            },
            'source' => $this->whenLoaded('source', fn (): ?string => $this->source?->name),
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ]),
            'first_response_at' => $this->first_response_at?->toIso8601String(),
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'next_follow_up_at' => $this->next_follow_up_at?->toIso8601String(),
            'duplicate_of_id' => $this->duplicate_of_id,
            'converted_at' => $this->converted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('leads.show', $this->id),
        ];
    }
}
