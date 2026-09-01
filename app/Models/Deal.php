<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Carbon\CarbonImmutable;
use Database\Factories\DealFactory;
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
 * One board for all three pipelines (D-005).
 *
 * `stage` is the deal's position on the board and is what the gate engine
 * guards; the underlying subject — an enquiry, a listing, a buyer requirement —
 * keeps its own lifecycle status. Conflating the two makes the board
 * undraggable without side effects.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $pipeline_id
 * @property int $stage_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $yacht_id
 * @property string $title
 * @property numeric $value
 * @property string $currency
 * @property CarbonImmutable|null $expected_close_date
 * @property int|null $assigned_user_id
 * @property CarbonImmutable|null $stage_entered_at
 * @property int|null $lost_reason_id
 * @property string|null $lost_notes
 * @property string $status
 * @property CarbonImmutable|null $closed_at
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
 * @property-read User|null $creator
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read LostReason|null $lostReason
 * @property-read Collection<int, Note> $notes
 * @property-read int|null $notes_count
 * @property-read Pipeline $pipeline
 * @property-read PipelineStage $stage
 * @property-read Model|null $subject
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User|null $updater
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\DealFactory factory($count = null, $state = [])
 * @method static Builder<static>|Deal newModelQuery()
 * @method static Builder<static>|Deal newQuery()
 * @method static Builder<static>|Deal onlyTrashed()
 * @method static Builder<static>|Deal open()
 * @method static Builder<static>|Deal query()
 * @method static Builder<static>|Deal search(string $term)
 * @method static Builder<static>|Deal whereAssignedUserId($value)
 * @method static Builder<static>|Deal whereClientId($value)
 * @method static Builder<static>|Deal whereClosedAt($value)
 * @method static Builder<static>|Deal whereCompanyId($value)
 * @method static Builder<static>|Deal whereCreatedAt($value)
 * @method static Builder<static>|Deal whereCreatedBy($value)
 * @method static Builder<static>|Deal whereCurrency($value)
 * @method static Builder<static>|Deal whereDeletedAt($value)
 * @method static Builder<static>|Deal whereExpectedCloseDate($value)
 * @method static Builder<static>|Deal whereId($value)
 * @method static Builder<static>|Deal whereLostNotes($value)
 * @method static Builder<static>|Deal whereLostReasonId($value)
 * @method static Builder<static>|Deal wherePipelineId($value)
 * @method static Builder<static>|Deal whereReference($value)
 * @method static Builder<static>|Deal whereStageEnteredAt($value)
 * @method static Builder<static>|Deal whereStageId($value)
 * @method static Builder<static>|Deal whereStatus($value)
 * @method static Builder<static>|Deal whereSubjectId($value)
 * @method static Builder<static>|Deal whereSubjectType($value)
 * @method static Builder<static>|Deal whereTitle($value)
 * @method static Builder<static>|Deal whereUpdatedAt($value)
 * @method static Builder<static>|Deal whereUpdatedBy($value)
 * @method static Builder<static>|Deal whereValue($value)
 * @method static Builder<static>|Deal whereYachtId($value)
 * @method static Builder<static>|Deal withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Deal withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ScopedToOwner::class])]
class Deal extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<DealFactory> */
    use HasFactory;

    /** @use HasOwnerScope<Deal> */
    use HasOwnerScope;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'gross_value' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'co_broker_amount' => 'decimal:2',
        'costs_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payouts_issued' => 'boolean',
        'receipts_generated' => 'boolean',
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'stage_entered_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'deal';
    }

    protected static function booted(): void
    {
        static::creating(function (Deal $deal): void {
            $deal->stage_entered_at ??= now();
        });
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsTo<Pipeline, $this> */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /** @return BelongsTo<PipelineStage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
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

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<LostReason, $this> */
    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(LostReason::class, 'lost_reason_id');
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /* ── behaviour ──────────────────────────────────────────────────────── */

    /**
     * Days the deal has sat in its current stage — the number that tells a
     * sales manager which cards have gone quiet.
     */
    public function daysInStage(): int
    {
        return (int) ($this->stage_entered_at?->diffInDays(now()) ?? 0);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Commission less what is paid away and what it cost to earn. The deal is
     * the one place the business asks "what did we actually make?", on any
     * line — charter, brokerage or management.
     */
    public function recalculate(): void
    {
        $this->net_amount = round(
            (float) $this->commission_amount - (float) $this->co_broker_amount - (float) $this->costs_amount,
            2,
        );
    }
}
