<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBlindIndex;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * Corporate clients, DMCs, concierges, charter partners and co-brokers.
 *
 * Not scoped to an owner: a DMC is a shared relationship, and hiding it from
 * half the team creates duplicates.
 *
 * @property int $id
 * @property string|null $reference
 * @property string $legal_name
 * @property string|null $trade_name
 * @property string $type
 * @property string|null $trn
 * @property string|null $trn_hash
 * @property string|null $trade_licence_no
 * @property CarbonImmutable|null $licence_expiry
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $emirate
 * @property string|null $country
 * @property string|null $billing_email
 * @property int $payment_terms_days
 * @property numeric|null $commission_rate_default
 * @property string $status
 * @property Collection<int, Note> $notes
 * @property int|null $assigned_user_id
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
 * @property-read Collection<int, Client> $clients
 * @property-read int|null $clients_count
 * @property-read Collection<int, CompanyContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read User|null $creator
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read int|null $notes_count
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static Builder<static>|Company newModelQuery()
 * @method static Builder<static>|Company newQuery()
 * @method static Builder<static>|Company onlyTrashed()
 * @method static Builder<static>|Company query()
 * @method static Builder<static>|Company search(string $term)
 * @method static Builder<static>|Company whereAddressLine1($value)
 * @method static Builder<static>|Company whereAddressLine2($value)
 * @method static Builder<static>|Company whereAssignedUserId($value)
 * @method static Builder<static>|Company whereBillingEmail($value)
 * @method static Builder<static>|Company whereBlind(string $field, string $value)
 * @method static Builder<static>|Company whereCity($value)
 * @method static Builder<static>|Company whereCommissionRateDefault($value)
 * @method static Builder<static>|Company whereCountry($value)
 * @method static Builder<static>|Company whereCreatedAt($value)
 * @method static Builder<static>|Company whereCreatedBy($value)
 * @method static Builder<static>|Company whereDeletedAt($value)
 * @method static Builder<static>|Company whereEmail($value)
 * @method static Builder<static>|Company whereEmirate($value)
 * @method static Builder<static>|Company whereId($value)
 * @method static Builder<static>|Company whereLegalName($value)
 * @method static Builder<static>|Company whereLicenceExpiry($value)
 * @method static Builder<static>|Company whereNotes($value)
 * @method static Builder<static>|Company wherePaymentTermsDays($value)
 * @method static Builder<static>|Company wherePhone($value)
 * @method static Builder<static>|Company whereReference($value)
 * @method static Builder<static>|Company whereStatus($value)
 * @method static Builder<static>|Company whereTradeLicenceNo($value)
 * @method static Builder<static>|Company whereTradeName($value)
 * @method static Builder<static>|Company whereTrn($value)
 * @method static Builder<static>|Company whereTrnHash($value)
 * @method static Builder<static>|Company whereType($value)
 * @method static Builder<static>|Company whereUpdatedAt($value)
 * @method static Builder<static>|Company whereUpdatedBy($value)
 * @method static Builder<static>|Company whereWebsite($value)
 * @method static Builder<static>|Company withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Company withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Company extends Model implements Auditable
{
    use AuditableTrait;
    use HasBlindIndex;

    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['trn', 'trn_hash'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'licence_expiry' => 'date',
        'commission_rate_default' => 'decimal:2',
        'trn' => 'encrypted',
    ];

    public function sequenceKey(): string
    {
        return 'client';
    }

    /** @return array<string, string> */
    public function blindIndexes(): array
    {
        return ['trn' => 'trn_hash'];
    }

    /** @return HasMany<Client, $this> */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /** @return HasMany<CompanyContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('legal_name', 'like', $like)
                ->orWhere('trade_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
