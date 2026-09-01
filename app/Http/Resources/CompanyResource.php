<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $canSeeTrn = $request->user()?->can('finance.view-amounts') ?? false;

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name,
            'display_name' => $this->displayName(),
            'type' => $this->type,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'city' => $this->city,
            'emirate' => $this->emirate,
            'country' => $this->country,
            'trade_licence_no' => $this->trade_licence_no,
            'licence_expiry' => $this->licence_expiry?->toDateString(),
            'licence_expiring' => $this->licence_expiry !== null
                && $this->licence_expiry->isFuture()
                && $this->licence_expiry->lte(now()->addDays(30)),
            'trn' => $this->when($canSeeTrn, fn () => $this->trn),
            'payment_terms_days' => $this->payment_terms_days,
            'commission_rate_default' => $this->commission_rate_default,
            'status' => $this->status,
            'status_tone' => $this->status === 'active' ? 'success' : 'neutral',
            'clients_count' => $this->whenCounted('clients'),
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('companies.show', $this->id),
        ];
    }
}
