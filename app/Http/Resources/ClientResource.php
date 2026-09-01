<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ClientResource extends JsonResource
{
    /**
     * Field-level protection happens here, not in React: a field the user may
     * not see is never serialised into the Inertia payload at all (brief §4).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canSeeVip = $request->user()?->can('viewVipFields', $this->resource) ?? false;

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'full_name' => $this->full_name,
            'full_name_ar' => $this->full_name_ar,
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'client_type' => $this->client_type ?? [],
            'position' => $this->position,

            'email' => $this->email,
            'mobile' => $this->mobile,
            'phone_alt' => $this->phone_alt,
            'preferred_channel' => $this->preferred_channel,

            'nationality' => $this->nationality,
            'city' => $this->city,
            'emirate' => $this->emirate,
            'country' => $this->country,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,

            'vip_level' => $this->vip_level,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'active' => 'success',
                'dormant' => 'neutral',
                'pending_approval' => 'warning',
                'blacklisted' => 'danger',
                default => 'neutral',
            },

            'kyc_status' => $this->kyc_status,
            'kyc_tone' => match ($this->kyc_status) {
                'verified' => 'success',
                'pending' => 'warning',
                'rejected', 'expired' => 'danger',
                default => 'neutral',
            },
            'kyc_verified_at' => $this->kyc_verified_at?->toIso8601String(),
            'kyc_expires_on' => $this->kyc_expires_on?->toDateString(),

            'company' => $this->whenLoaded('company', fn (): ?array => $this->company === null ? null : [
                'id' => $this->company->id,
                'name' => $this->company->displayName(),
            ]),

            'assignee' => $this->whenLoaded('assignee', fn (): ?array => $this->assignee === null ? null : [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),

            // VIP group: passport, EID, DOB, dietary, allergies, VIP notes.
            // Reading any of it writes a record_access_logs row.
            'passport_number' => $this->when($canSeeVip, fn () => $this->passport_number),
            'passport_expiry' => $this->when($canSeeVip, fn () => $this->passport_expiry?->toDateString()),
            'emirates_id' => $this->when($canSeeVip, fn () => $this->emirates_id),
            'date_of_birth' => $this->when($canSeeVip, fn () => $this->date_of_birth?->toDateString()),
            'dietary_preferences' => $this->when($canSeeVip, fn () => $this->dietary_preferences),
            'allergies' => $this->when($canSeeVip, fn () => $this->allergies),
            'notes_vip' => $this->when($canSeeVip, fn () => $this->notes_vip),
            'vip_fields_hidden' => ! $canSeeVip && $this->isVip(),

            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            'url' => route('clients.show', $this->id),
        ];
    }
}
