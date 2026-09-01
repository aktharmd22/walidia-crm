<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\YachtFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * One hull, three capability flags (D-003).
 *
 * The same 40-metre yacht is routinely chartered, listed for sale and managed
 * at the same time. Splitting that across three tables guarantees three
 * versions of its specs, photos and availability — so the commercial terms live
 * in profiles hanging off this record instead.
 *
 * @property int $id
 * @property string|null $reference
 * @property string $name
 * @property string|null $name_ar
 * @property bool $is_charter_fleet
 * @property bool $is_for_sale
 * @property bool $is_managed
 * @property string|null $builder
 * @property string|null $model
 * @property int|null $year_built
 * @property int|null $year_refit
 * @property numeric|null $loa_m
 * @property numeric|null $beam_m
 * @property numeric|null $draft_m
 * @property int|null $gross_tonnage
 * @property string|null $hull_material
 * @property string|null $exterior_designer
 * @property string|null $interior_designer
 * @property string|null $engines
 * @property int|null $engine_hours
 * @property int|null $cruising_speed_kn
 * @property int|null $max_speed_kn
 * @property int|null $fuel_capacity_l
 * @property int|null $water_capacity_l
 * @property int|null $capacity_static
 * @property int|null $capacity_cruising
 * @property int|null $cabins
 * @property int|null $berths
 * @property int|null $crew_count
 * @property string|null $flag_country
 * @property string|null $registration_no
 * @property string|null $imo_no
 * @property string|null $mmsi
 * @property int|null $home_marina_id
 * @property int|null $berth_id
 * @property int|null $owner_client_id
 * @property string|null $description
 * @property string|null $description_ar
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Collection<int, YachtAvailabilityBlock> $availabilityBlocks
 * @property-read int|null $availability_blocks_count
 * @property-read Berth|null $berth
 * @property-read YachtCharterProfile|null $charterProfile
 * @property-read User|null $creator
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read Marina|null $homeMarina
 * @property-read Collection<int, YachtInventoryItem> $inventory
 * @property-read int|null $inventory_count
 * @property-read YachtManagementProfile|null $managementProfile
 * @property-read Collection<int, YachtMedia> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Note> $notes
 * @property-read int|null $notes_count
 * @property-read Collection<int, OwnerAgreement> $ownerAgreements
 * @property-read int|null $owner_agreements_count
 * @property-read Collection<int, Client> $owners
 * @property-read int|null $owners_count
 * @property-read YachtSaleProfile|null $saleProfile
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $updater
 *
 * @method static Builder<static>|Yacht charterFleet()
 * @method static \Database\Factories\YachtFactory factory($count = null, $state = [])
 * @method static Builder<static>|Yacht forSale()
 * @method static Builder<static>|Yacht managed()
 * @method static Builder<static>|Yacht newModelQuery()
 * @method static Builder<static>|Yacht newQuery()
 * @method static Builder<static>|Yacht onlyTrashed()
 * @method static Builder<static>|Yacht query()
 * @method static Builder<static>|Yacht search(string $term)
 * @method static Builder<static>|Yacht whereBeamM($value)
 * @method static Builder<static>|Yacht whereBerthId($value)
 * @method static Builder<static>|Yacht whereBerths($value)
 * @method static Builder<static>|Yacht whereBuilder($value)
 * @method static Builder<static>|Yacht whereCabins($value)
 * @method static Builder<static>|Yacht whereCapacityCruising($value)
 * @method static Builder<static>|Yacht whereCapacityStatic($value)
 * @method static Builder<static>|Yacht whereCreatedAt($value)
 * @method static Builder<static>|Yacht whereCreatedBy($value)
 * @method static Builder<static>|Yacht whereCrewCount($value)
 * @method static Builder<static>|Yacht whereCruisingSpeedKn($value)
 * @method static Builder<static>|Yacht whereDeletedAt($value)
 * @method static Builder<static>|Yacht whereDescription($value)
 * @method static Builder<static>|Yacht whereDescriptionAr($value)
 * @method static Builder<static>|Yacht whereDraftM($value)
 * @method static Builder<static>|Yacht whereEngineHours($value)
 * @method static Builder<static>|Yacht whereEngines($value)
 * @method static Builder<static>|Yacht whereExteriorDesigner($value)
 * @method static Builder<static>|Yacht whereFlagCountry($value)
 * @method static Builder<static>|Yacht whereFuelCapacityL($value)
 * @method static Builder<static>|Yacht whereGrossTonnage($value)
 * @method static Builder<static>|Yacht whereHomeMarinaId($value)
 * @method static Builder<static>|Yacht whereHullMaterial($value)
 * @method static Builder<static>|Yacht whereId($value)
 * @method static Builder<static>|Yacht whereImoNo($value)
 * @method static Builder<static>|Yacht whereInteriorDesigner($value)
 * @method static Builder<static>|Yacht whereIsCharterFleet($value)
 * @method static Builder<static>|Yacht whereIsForSale($value)
 * @method static Builder<static>|Yacht whereIsManaged($value)
 * @method static Builder<static>|Yacht whereLoaM($value)
 * @method static Builder<static>|Yacht whereMaxSpeedKn($value)
 * @method static Builder<static>|Yacht whereMmsi($value)
 * @method static Builder<static>|Yacht whereModel($value)
 * @method static Builder<static>|Yacht whereName($value)
 * @method static Builder<static>|Yacht whereNameAr($value)
 * @method static Builder<static>|Yacht whereOwnerClientId($value)
 * @method static Builder<static>|Yacht whereReference($value)
 * @method static Builder<static>|Yacht whereRegistrationNo($value)
 * @method static Builder<static>|Yacht whereStatus($value)
 * @method static Builder<static>|Yacht whereUpdatedAt($value)
 * @method static Builder<static>|Yacht whereUpdatedBy($value)
 * @method static Builder<static>|Yacht whereWaterCapacityL($value)
 * @method static Builder<static>|Yacht whereYearBuilt($value)
 * @method static Builder<static>|Yacht whereYearRefit($value)
 * @method static Builder<static>|Yacht withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Yacht withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Yacht extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtFactory> */
    use HasFactory;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_charter_fleet' => 'boolean',
            'is_for_sale' => 'boolean',
            'is_managed' => 'boolean',
            'loa_m' => 'decimal:2',
            'beam_m' => 'decimal:2',
            'draft_m' => 'decimal:2',
        ];
    }

    public function sequenceKey(): string
    {
        return 'yacht';
    }

    /* ── profiles ───────────────────────────────────────────────────────── */

    /** @return HasOne<YachtCharterProfile, $this> */
    public function charterProfile(): HasOne
    {
        return $this->hasOne(YachtCharterProfile::class);
    }

    /** @return HasOne<YachtSaleProfile, $this> */
    public function saleProfile(): HasOne
    {
        return $this->hasOne(YachtSaleProfile::class);
    }

    /** @return HasOne<YachtManagementProfile, $this> */
    public function managementProfile(): HasOne
    {
        return $this->hasOne(YachtManagementProfile::class);
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsTo<Marina, $this> */
    public function homeMarina(): BelongsTo
    {
        return $this->belongsTo(Marina::class, 'home_marina_id');
    }

    /** @return BelongsTo<Berth, $this> */
    public function berth(): BelongsTo
    {
        return $this->belongsTo(Berth::class);
    }

    /** @return HasMany<YachtMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(YachtMedia::class)->orderBy('sort_order');
    }

    /** @return HasMany<YachtInventoryItem, $this> */
    public function inventory(): HasMany
    {
        return $this->hasMany(YachtInventoryItem::class);
    }

    /** @return HasMany<YachtAvailabilityBlock, $this> */
    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(YachtAvailabilityBlock::class);
    }

    /** @return BelongsToMany<Client, $this> */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'yacht_owners')
            ->withPivot(['ownership_percentage', 'is_primary', 'since', 'until'])
            ->withTimestamps();
    }

    /** @return HasMany<OwnerAgreement, $this> */
    public function ownerAgreements(): HasMany
    {
        return $this->hasMany(OwnerAgreement::class);
    }

    /* ── availability ───────────────────────────────────────────────────── */

    /**
     * Is the yacht free for this window? Reads the one table that owns fleet
     * occupancy, so bookings, option holds, maintenance and owner use are all
     * considered by a single question.
     */
    public function isAvailableBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?int $ignoreBlockId = null): bool
    {
        return ! $this->availabilityBlocks()
            ->when($ignoreBlockId, fn (Builder $query) => $query->whereKeyNot($ignoreBlockId))
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->exists();
    }

    public function heroImageUrl(): ?string
    {
        $hero = $this->media->firstWhere('collection', 'hero') ?? $this->media->first();

        return $hero?->url();
    }

    /* ── query scopes ───────────────────────────────────────────────────── */

    /**
     * @param  Builder<Yacht>  $query
     * @return Builder<Yacht>
     */
    public function scopeCharterFleet(Builder $query): Builder
    {
        return $query->where('is_charter_fleet', true);
    }

    /**
     * @param  Builder<Yacht>  $query
     * @return Builder<Yacht>
     */
    public function scopeForSale(Builder $query): Builder
    {
        return $query->where('is_for_sale', true);
    }

    /**
     * @param  Builder<Yacht>  $query
     * @return Builder<Yacht>
     */
    public function scopeManaged(Builder $query): Builder
    {
        return $query->where('is_managed', true);
    }

    /**
     * @param  Builder<Yacht>  $query
     * @return Builder<Yacht>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('name', 'like', $like)
                ->orWhere('name_ar', 'like', $like)
                ->orWhere('builder', 'like', $like)
                ->orWhere('model', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
