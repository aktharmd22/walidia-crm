<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One timeline entry, shaped once so every record screen renders history the
 * same way.
 *
 * @mixin Activity
 */
class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'direction' => $this->direction,
            'summary' => $this->summary,
            'body' => $this->body,
            'user' => $this->whenLoaded('user', fn (): ?string => $this->user?->name),
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'editable' => $this->isEditableBy($request->user()),
        ];
    }
}
