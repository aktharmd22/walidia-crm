<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\MarinaFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * A marina carries its own timezone: Seychelles and the Maldives share a fleet
 * calendar with the UAE, and charter instants are derived from the departure
 * marina rather than assumed (D-010).
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_ar
 * @property string $country
 * @property string|null $emirate
 * @property string|null $city
 * @property string $timezone
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property bool $requires_manifest
 * @property string|null $manifest_format
 * @property string|null $notes
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Collection<int, Berth> $berths
 * @property-read int|null $berths_count
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Yacht> $yachts
 * @property-read int|null $yachts_count
 *
 * @method static \Database\Factories\MarinaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereEmirate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereManifestFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereRequiresManifest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Marina extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<MarinaFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requires_manifest' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /** @return HasMany<Berth, $this> */
    public function berths(): HasMany
    {
        return $this->hasMany(Berth::class);
    }

    /** @return HasMany<Yacht, $this> */
    public function yachts(): HasMany
    {
        return $this->hasMany(Yacht::class, 'home_marina_id');
    }
}
