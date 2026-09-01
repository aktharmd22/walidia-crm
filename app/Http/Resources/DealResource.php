<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Deal
 */
class DealResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $canSeeValue = $request->user()?->can('finance.view-amounts')
            ?? false;

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'value' => $this->when($canSeeValue || $this->assigned_user_id === $request->user()?->id, fn () => $this->value),
            'currency' => $this->currency,
            'status' => $this->status,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'days_in_stage' => $this->daysInStage(),
            'stage' => $this->whenLoaded('stage', fn (): array => [
                'id' => $this->stage->id,
                'key' => $this->stage->key,
                'name' => $this->stage->name,
                'tone' => $this->stage->colour_token,
                'probability' => $this->stage->probability,
            ]),
            'pipeline' => $this->whenLoaded('pipeline', fn (): array => [
                'id' => $this->pipeline->id,
                'key' => $this->pipeline->key,
                'name' => $this->pipeline->name,
            ]),
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->full_name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'yacht' => $this->whenLoaded('yacht', fn (): ?array => $this->yacht === null ? null : [
                'id' => $this->yacht->id,
                'name' => $this->yacht->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('deals.show', $this->id),
        ];
    }
}
