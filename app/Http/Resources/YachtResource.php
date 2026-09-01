<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Yacht;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Yacht
 */
class YachtResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $canSeeRates = $request->user()?->can('viewRates', $this->resource) ?? false;

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'builder' => $this->builder,
            'model' => $this->model,
            'year_built' => $this->year_built,
            'year_refit' => $this->year_refit,

            'loa_m' => $this->loa_m,
            'beam_m' => $this->beam_m,
            'draft_m' => $this->draft_m,
            'gross_tonnage' => $this->gross_tonnage,
            'engines' => $this->engines,
            'engine_hours' => $this->engine_hours,
            'cruising_speed_kn' => $this->cruising_speed_kn,
            'max_speed_kn' => $this->max_speed_kn,

            'capacity_static' => $this->capacity_static,
            'capacity_cruising' => $this->capacity_cruising,
            'cabins' => $this->cabins,
            'berths' => $this->berths,
            'crew_count' => $this->crew_count,

            'flag_country' => $this->flag_country,
            'registration_no' => $this->registration_no,
            'imo_no' => $this->imo_no,

            'is_charter_fleet' => $this->is_charter_fleet,
            'is_for_sale' => $this->is_for_sale,
            'is_managed' => $this->is_managed,
            'roles' => array_values(array_filter([
                $this->is_charter_fleet ? 'Charter' : null,
                $this->is_for_sale ? 'For sale' : null,
                $this->is_managed ? 'Managed' : null,
            ])),

            'status' => $this->status,
            'status_tone' => match ($this->status) {
                'active' => 'success',
                'maintenance' => 'warning',
                'off_market' => 'neutral',
                'sold' => 'info',
                default => 'neutral',
            },

            'home_marina' => $this->whenLoaded('homeMarina', fn (): ?array => $this->homeMarina === null ? null : [
                'id' => $this->homeMarina->id,
                'name' => $this->homeMarina->name,
                'timezone' => $this->homeMarina->timezone,
            ]),

            'charter_rates' => $this->when(
                $canSeeRates && $this->relationLoaded('charterProfile') && $this->charterProfile !== null,
                fn (): array => [
                    'hourly_rate' => $this->charterProfile->hourly_rate,
                    'half_day_rate' => $this->charterProfile->half_day_rate,
                    'full_day_rate' => $this->charterProfile->full_day_rate,
                    'overnight_rate' => $this->charterProfile->overnight_rate,
                    'currency' => $this->charterProfile->currency,
                    'min_hours' => $this->charterProfile->min_hours,
                    'is_bookable' => $this->charterProfile->is_bookable,
                ],
            ),

            'asking_price' => $this->when(
                $canSeeRates && $this->relationLoaded('saleProfile') && $this->saleProfile !== null,
                fn () => $this->saleProfile->asking_price,
            ),

            'hero_image' => $this->whenLoaded('media', fn (): ?string => $this->heroImageUrl()),
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'url' => route('fleet.yachts.show', $this->id),
        ];
    }
}
