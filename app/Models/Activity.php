<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One timeline entry. Calls, messages, meetings, status changes and gate
 * evaluations all land here, so a client's history reads as one story.
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $client_id
 * @property int|null $user_id
 * @property string $type
 * @property string|null $direction
 * @property string $summary
 * @property string|null $body
 * @property array<array-key, mixed>|null $meta
 * @property int|null $communication_id
 * @property CarbonImmutable $occurred_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Client|null $client
 * @property-read Model $subject
 * @property-read User|null $user
 *
 * @method static \Database\Factories\ActivityFactory factory($count = null, $state = [])
 * @method static Builder<static>|Activity newModelQuery()
 * @method static Builder<static>|Activity newQuery()
 * @method static Builder<static>|Activity ofType(string $type)
 * @method static Builder<static>|Activity onlyTrashed()
 * @method static Builder<static>|Activity query()
 * @method static Builder<static>|Activity whereBody($value)
 * @method static Builder<static>|Activity whereClientId($value)
 * @method static Builder<static>|Activity whereCommunicationId($value)
 * @method static Builder<static>|Activity whereCreatedAt($value)
 * @method static Builder<static>|Activity whereDeletedAt($value)
 * @method static Builder<static>|Activity whereDirection($value)
 * @method static Builder<static>|Activity whereId($value)
 * @method static Builder<static>|Activity whereMeta($value)
 * @method static Builder<static>|Activity whereOccurredAt($value)
 * @method static Builder<static>|Activity whereSubjectId($value)
 * @method static Builder<static>|Activity whereSubjectType($value)
 * @method static Builder<static>|Activity whereSummary($value)
 * @method static Builder<static>|Activity whereType($value)
 * @method static Builder<static>|Activity whereUpdatedAt($value)
 * @method static Builder<static>|Activity whereUserId($value)
 * @method static Builder<static>|Activity withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Activity withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'occurred_at' => 'datetime'];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Author edits are allowed for 24 hours, then the entry is history.
     */
    public function isEditableBy(?User $user): bool
    {
        return $user !== null
            && $this->user_id === $user->id
            && $this->created_at?->gt(now()->subDay()) === true;
    }

    /**
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
