<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Carbon\CarbonImmutable;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * @property int $id
 * @property string|null $reference
 * @property string $business_line
 * @property int|null $source_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $message
 * @property array<array-key, mixed>|null $meta
 * @property string $status
 * @property int|null $assigned_user_id
 * @property int|null $duplicate_of_id
 * @property int|null $duplicate_score
 * @property CarbonImmutable|null $duplicate_checked_at
 * @property CarbonImmutable|null $first_response_at
 * @property CarbonImmutable|null $sla_due_at
 * @property CarbonImmutable|null $next_follow_up_at
 * @property CarbonImmutable|null $converted_at
 * @property string|null $converted_to_type
 * @property int|null $converted_to_id
 * @property string|null $unqualified_reason
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
 * @property-read Client|null $client
 * @property-read Company|null $company
 * @property-read Model|null $convertedTo
 * @property-read User|null $creator
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read Lead|null $duplicateOf
 * @property-read Collection<int, Note> $notes
 * @property-read int|null $notes_count
 * @property-read LeadSource|null $source
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\LeadFactory factory($count = null, $state = [])
 * @method static Builder<static>|Lead newModelQuery()
 * @method static Builder<static>|Lead newQuery()
 * @method static Builder<static>|Lead onlyTrashed()
 * @method static Builder<static>|Lead query()
 * @method static Builder<static>|Lead search(string $term)
 * @method static Builder<static>|Lead whereAssignedUserId($value)
 * @method static Builder<static>|Lead whereBusinessLine($value)
 * @method static Builder<static>|Lead whereClientId($value)
 * @method static Builder<static>|Lead whereCompanyId($value)
 * @method static Builder<static>|Lead whereConvertedAt($value)
 * @method static Builder<static>|Lead whereConvertedToId($value)
 * @method static Builder<static>|Lead whereConvertedToType($value)
 * @method static Builder<static>|Lead whereCreatedAt($value)
 * @method static Builder<static>|Lead whereCreatedBy($value)
 * @method static Builder<static>|Lead whereDeletedAt($value)
 * @method static Builder<static>|Lead whereDuplicateCheckedAt($value)
 * @method static Builder<static>|Lead whereDuplicateOfId($value)
 * @method static Builder<static>|Lead whereDuplicateScore($value)
 * @method static Builder<static>|Lead whereEmail($value)
 * @method static Builder<static>|Lead whereFirstResponseAt($value)
 * @method static Builder<static>|Lead whereId($value)
 * @method static Builder<static>|Lead whereMessage($value)
 * @method static Builder<static>|Lead whereMeta($value)
 * @method static Builder<static>|Lead whereMobile($value)
 * @method static Builder<static>|Lead whereName($value)
 * @method static Builder<static>|Lead whereNextFollowUpAt($value)
 * @method static Builder<static>|Lead whereReference($value)
 * @method static Builder<static>|Lead whereSlaDueAt($value)
 * @method static Builder<static>|Lead whereSourceId($value)
 * @method static Builder<static>|Lead whereStatus($value)
 * @method static Builder<static>|Lead whereUnqualifiedReason($value)
 * @method static Builder<static>|Lead whereUpdatedAt($value)
 * @method static Builder<static>|Lead whereUpdatedBy($value)
 * @method static Builder<static>|Lead withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Lead withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ScopedToOwner::class])]
class Lead extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /** @use HasOwnerScope<Lead> */
    use HasOwnerScope;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** The Unassigned pool is a shared queue, so unowned leads stay visible. */
    public bool $ownerScopeIncludesUnassigned = true;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
        'first_response_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'duplicate_checked_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'lead';
    }

    /** @return BelongsTo<LeadSource, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

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

    /** @return BelongsTo<Lead, $this> */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'duplicate_of_id');
    }

    /** @return MorphTo<Model, $this> */
    public function convertedTo(): MorphTo
    {
        return $this->morphTo('converted_to');
    }

    public function isOverdue(): bool
    {
        return $this->sla_due_at !== null
            && $this->first_response_at === null
            && $this->sla_due_at->isPast();
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
