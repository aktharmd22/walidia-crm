<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBlindIndex;
use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Carbon\CarbonImmutable;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * The single client record.
 *
 * One row can be a charter guest, a buyer, a seller and an owner at the same
 * time — client_type is an array, not an enum, because the alternative is four
 * records for one person and a reconciliation problem forever.
 *
 * @property int $id
 * @property string|null $reference
 * @property array<array-key, mixed>|null $client_type
 * @property string|null $salutation
 * @property string $first_name
 * @property string|null $last_name
 * @property string $full_name
 * @property string|null $full_name_ar
 * @property int|null $company_id
 * @property string|null $position
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $phone_alt
 * @property string $preferred_channel
 * @property string|null $nationality
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $emirate
 * @property string|null $country
 * @property CarbonImmutable|null $date_of_birth
 * @property string|null $passport_number
 * @property string|null $passport_hash
 * @property CarbonImmutable|null $passport_expiry
 * @property string|null $emirates_id
 * @property string|null $emirates_id_hash
 * @property string|null $trn
 * @property string $vip_level
 * @property string|null $dietary_preferences
 * @property string|null $allergies
 * @property string|null $notes_vip
 * @property Collection<int, Note> $notes
 * @property int|null $source_id
 * @property int|null $assigned_user_id
 * @property string $kyc_status
 * @property CarbonImmutable|null $kyc_verified_at
 * @property int|null $kyc_verified_by
 * @property CarbonImmutable|null $kyc_expires_on
 * @property string $aml_status
 * @property CarbonImmutable|null $aml_screened_at
 * @property CarbonImmutable|null $marketing_consent_at
 * @property string|null $consent_channel
 * @property string $status
 * @property CarbonImmutable|null $approved_at
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $assignee
 * @property-read Collection<int, Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Company|null $company
 * @property-read Collection<int, ClientContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read User|null $creator
 * @property-read Collection<int, Deal> $deals
 * @property-read int|null $deals_count
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read Collection<int, Lead> $leads
 * @property-read int|null $leads_count
 * @property-read int|null $notes_count
 * @property-read Collection<int, Yacht> $ownedYachts
 * @property-read int|null $owned_yachts_count
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static Builder<static>|Client newModelQuery()
 * @method static Builder<static>|Client newQuery()
 * @method static Builder<static>|Client ofType(string $type)
 * @method static Builder<static>|Client onlyTrashed()
 * @method static Builder<static>|Client query()
 * @method static Builder<static>|Client search(string $term)
 * @method static Builder<static>|Client whereAddressLine1($value)
 * @method static Builder<static>|Client whereAddressLine2($value)
 * @method static Builder<static>|Client whereAllergies($value)
 * @method static Builder<static>|Client whereAmlScreenedAt($value)
 * @method static Builder<static>|Client whereAmlStatus($value)
 * @method static Builder<static>|Client whereApprovedAt($value)
 * @method static Builder<static>|Client whereApprovedBy($value)
 * @method static Builder<static>|Client whereAssignedUserId($value)
 * @method static Builder<static>|Client whereBlind(string $field, string $value)
 * @method static Builder<static>|Client whereCity($value)
 * @method static Builder<static>|Client whereClientType($value)
 * @method static Builder<static>|Client whereCompanyId($value)
 * @method static Builder<static>|Client whereConsentChannel($value)
 * @method static Builder<static>|Client whereCountry($value)
 * @method static Builder<static>|Client whereCreatedAt($value)
 * @method static Builder<static>|Client whereCreatedBy($value)
 * @method static Builder<static>|Client whereDateOfBirth($value)
 * @method static Builder<static>|Client whereDeletedAt($value)
 * @method static Builder<static>|Client whereDietaryPreferences($value)
 * @method static Builder<static>|Client whereEmail($value)
 * @method static Builder<static>|Client whereEmirate($value)
 * @method static Builder<static>|Client whereEmiratesId($value)
 * @method static Builder<static>|Client whereEmiratesIdHash($value)
 * @method static Builder<static>|Client whereFirstName($value)
 * @method static Builder<static>|Client whereFullName($value)
 * @method static Builder<static>|Client whereFullNameAr($value)
 * @method static Builder<static>|Client whereId($value)
 * @method static Builder<static>|Client whereKycExpiresOn($value)
 * @method static Builder<static>|Client whereKycStatus($value)
 * @method static Builder<static>|Client whereKycVerifiedAt($value)
 * @method static Builder<static>|Client whereKycVerifiedBy($value)
 * @method static Builder<static>|Client whereLastName($value)
 * @method static Builder<static>|Client whereMarketingConsentAt($value)
 * @method static Builder<static>|Client whereMobile($value)
 * @method static Builder<static>|Client whereNationality($value)
 * @method static Builder<static>|Client whereNotes($value)
 * @method static Builder<static>|Client whereNotesVip($value)
 * @method static Builder<static>|Client wherePassportExpiry($value)
 * @method static Builder<static>|Client wherePassportHash($value)
 * @method static Builder<static>|Client wherePassportNumber($value)
 * @method static Builder<static>|Client wherePhoneAlt($value)
 * @method static Builder<static>|Client wherePosition($value)
 * @method static Builder<static>|Client wherePreferredChannel($value)
 * @method static Builder<static>|Client whereReference($value)
 * @method static Builder<static>|Client whereSalutation($value)
 * @method static Builder<static>|Client whereSourceId($value)
 * @method static Builder<static>|Client whereStatus($value)
 * @method static Builder<static>|Client whereTrn($value)
 * @method static Builder<static>|Client whereUpdatedAt($value)
 * @method static Builder<static>|Client whereUpdatedBy($value)
 * @method static Builder<static>|Client whereVipLevel($value)
 * @method static Builder<static>|Client withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Client withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ScopedToOwner::class])]
class Client extends Model implements Auditable
{
    use AuditableTrait;
    use HasBlindIndex;

    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /** @use HasOwnerScope<Client> */
    use HasOwnerScope;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = [
        'passport_number', 'passport_hash', 'emirates_id', 'emirates_id_hash',
        'trn', 'dietary_preferences', 'allergies', 'notes_vip',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_type' => 'array',
        'date_of_birth' => 'date',
        'passport_expiry' => 'date',
        'kyc_expires_on' => 'date',
        'kyc_verified_at' => 'datetime',
        'aml_screened_at' => 'datetime',
        'marketing_consent_at' => 'datetime',
        'approved_at' => 'datetime',
        // Encrypted at rest (brief §4).,
        'passport_number' => 'encrypted',
        'emirates_id' => 'encrypted',
        'trn' => 'encrypted',
        'dietary_preferences' => 'encrypted',
        'allergies' => 'encrypted',
        'notes_vip' => 'encrypted',
    ];

    public function sequenceKey(): string
    {
        return 'client';
    }

    /**
     * @return array<string, string>
     */
    public function blindIndexes(): array
    {
        return [
            'passport_number' => 'passport_hash',
            'emirates_id' => 'emirates_id_hash',
        ];
    }

    protected static function booted(): void
    {
        // full_name is denormalised for search and display; keeping it in sync
        // here means no screen ever has to concatenate names itself.
        static::saving(function (Client $client): void {
            $client->full_name = trim(implode(' ', array_filter([$client->first_name, $client->last_name])));
        });
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<ClientContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /** @return BelongsToMany<Yacht, $this> */
    public function ownedYachts(): BelongsToMany
    {
        return $this->belongsToMany(Yacht::class, 'yacht_owners')
            ->withPivot(['ownership_percentage', 'is_primary', 'since', 'until'])
            ->withTimestamps();
    }

    /* ── state ──────────────────────────────────────────────────────────── */

    public function isVip(): bool
    {
        return in_array($this->vip_level, ['vip', 'uhnw', 'protected'], true);
    }

    public function kycIsValid(): bool
    {
        return $this->kyc_status === 'verified'
            && ($this->kyc_expires_on === null || $this->kyc_expires_on->isFuture());
    }

    public function timelineClientId(): ?int
    {
        return $this->id;
    }

    /* ── query scopes ───────────────────────────────────────────────────── */

    /**
     * @param  Builder<Client>  $query
     * @return Builder<Client>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('client_type', $type);
    }

    /**
     * @param  Builder<Client>  $query
     * @return Builder<Client>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('full_name', 'like', $like)
                ->orWhere('full_name_ar', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
