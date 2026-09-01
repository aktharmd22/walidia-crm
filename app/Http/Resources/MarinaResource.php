<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Marina;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Marina
 */
class MarinaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'country' => $this->country,
            'emirate' => $this->emirate,
            'city' => $this->city,
            'timezone' => $this->timezone,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'requires_manifest' => $this->requires_manifest,
            'manifest_format' => $this->manifest_format,
            'is_active' => $this->is_active,
            'status_tone' => $this->is_active ? 'success' : 'neutral',
            'berths_count' => $this->whenCounted('berths'),
            'yachts_count' => $this->whenCounted('yachts'),
            'url' => route('fleet.marinas.show', $this->id),
        ];
    }
}
