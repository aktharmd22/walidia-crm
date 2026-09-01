<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\TaskFactory;
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
 * The "Next Action" object from the flowcharts. Created by hand, by a workflow,
 * or by a soft gate that wants someone to look at something.
 *
 * @property int $id
 * @property string|null $reference
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property string $priority
 * @property int|null $assigned_user_id
 * @property string|null $assigned_role
 * @property CarbonImmutable|null $due_at
 * @property string $status
 * @property CarbonImmutable|null $completed_at
 * @property int|null $completed_by
 * @property CarbonImmutable|null $escalate_at
 * @property int|null $escalated_to
 * @property CarbonImmutable|null $escalated_at
 * @property string $source
 * @property string|null $source_key
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User|null $assignee
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read User|null $creator
 * @property-read Model|null $subject
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static Builder<static>|Task newModelQuery()
 * @method static Builder<static>|Task newQuery()
 * @method static Builder<static>|Task onlyTrashed()
 * @method static Builder<static>|Task open()
 * @method static Builder<static>|Task overdue()
 * @method static Builder<static>|Task query()
 * @method static Builder<static>|Task search(string $term)
 * @method static Builder<static>|Task whereAssignedRole($value)
 * @method static Builder<static>|Task whereAssignedUserId($value)
 * @method static Builder<static>|Task whereCompletedAt($value)
 * @method static Builder<static>|Task whereCompletedBy($value)
 * @method static Builder<static>|Task whereCreatedAt($value)
 * @method static Builder<static>|Task whereCreatedBy($value)
 * @method static Builder<static>|Task whereDeletedAt($value)
 * @method static Builder<static>|Task whereDescription($value)
 * @method static Builder<static>|Task whereDueAt($value)
 * @method static Builder<static>|Task whereEscalateAt($value)
 * @method static Builder<static>|Task whereEscalatedAt($value)
 * @method static Builder<static>|Task whereEscalatedTo($value)
 * @method static Builder<static>|Task whereId($value)
 * @method static Builder<static>|Task wherePriority($value)
 * @method static Builder<static>|Task whereReference($value)
 * @method static Builder<static>|Task whereSource($value)
 * @method static Builder<static>|Task whereSourceKey($value)
 * @method static Builder<static>|Task whereStatus($value)
 * @method static Builder<static>|Task whereSubjectId($value)
 * @method static Builder<static>|Task whereSubjectType($value)
 * @method static Builder<static>|Task whereTitle($value)
 * @method static Builder<static>|Task whereType($value)
 * @method static Builder<static>|Task whereUpdatedAt($value)
 * @method static Builder<static>|Task whereUpdatedBy($value)
 * @method static Builder<static>|Task withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Task withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Task extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'escalate_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    public function sequenceKey(): string
    {
        return 'task';
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->due_at !== null && $this->due_at->isPast();
    }

    /**
     * A readable name for whatever this task hangs off, without every screen
     * needing to know how each subject names itself.
     */
    public function subjectLabel(): ?string
    {
        $subject = $this->subject;

        if ($subject === null) {
            return null;
        }

        return match (true) {
            isset($subject->full_name) => (string) $subject->full_name,
            isset($subject->title) => (string) $subject->title,
            isset($subject->name) => (string) $subject->name,
            isset($subject->reference) => (string) $subject->reference,
            default => null,
        };
    }

    public function complete(?User $by = null): void
    {
        $this->forceFill([
            'status' => 'done',
            'completed_at' => now(),
            'completed_by' => $by instanceof User ? $by->id : auth()->id(),
        ])->save();
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('due_at')->where('due_at', '<', now());
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)->orWhere('reference', 'like', $like);
        });
    }
}
