<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ManagementAgreement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ManagementAgreement
 */
class ManagementAgreementResource extends JsonResource
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
            'yacht_owner_id' => $this->yacht_owner_id,
            'assigned_user_id' => $this->assigned_user_id,
            'scope' => $this->scope,
            'fee_model' => $this->fee_model,
            'monthly_fee' => $this->when($request->user()?->can('finance.view-amounts') ?? false, fn () => $this->monthly_fee),
            'fee_percentage' => $this->fee_percentage,
            'currency' => $this->currency,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'notice_days' => $this->notice_days,
            'opex_budget_annual' => $this->opex_budget_annual,
            'is_expiring' => $this->isExpiring(),
            'notes' => $this->notes,
            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'active' => 'success',
                'expiring' => 'warning',
                'ended' => 'neutral',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('management.agreements.show', $this->id),
        ];
    }
}
