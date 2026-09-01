<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vendor
 */
class VendorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name,
            'vendor_category_id' => $this->vendor_category_id,
            'trn' => $this->trn,
            'trade_licence_no' => $this->trade_licence_no,
            'notes' => $this->notes,
            'display_name' => $this->displayName(),
            'category' => $this->whenLoaded('category', fn (): ?string => $this->category?->name),
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'payment_terms_days' => $this->payment_terms_days,
            'licence_expiry' => $this->licence_expiry?->toDateString(),
            'rating_avg' => $this->rating_avg,
            'is_approved' => $this->is_approved,
            'status' => $this->status,
            'status_tone' => $this->is_approved ? 'success' : 'warning',
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('vendors.show', $this->id),
        ];
    }
}
