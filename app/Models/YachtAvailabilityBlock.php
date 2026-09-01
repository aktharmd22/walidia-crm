<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtAvailabilityBlockFactory;
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
 * The single writer of fleet occupancy.
 *
 * Bookings, option holds, maintenance windows and owner use all create a block
 * here, so "is this yacht free?" is one question against one table rather than
 * four joins that will eventually disagree.
 *
 * @property int $id
 * @property int $yacht_id
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property string $type
 * @property string|null $source_type
 * @property int|null $source_id
 * @property CarbonImmutable|null $expires_at
 * @property string|null $note
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Model|null $source
 * @property-read Yacht|null $yacht
 *
 * @method static Builder<static>|YachtAvailabilityBlock effective()
 * @method static \Database\Factories\YachtAvailabilityBlockFactory factory($count = null, $state = [])
 * @method static Builder<static>|YachtAvailabilityBlock newModelQuery()
 * @method static Builder<static>|YachtAvailabilityBlock newQuery()
 * @method static Builder<static>|YachtAvailabilityBlock onlyTrashed()
 * @method static Builder<static>|YachtAvailabilityBlock overlapping(\DateTimeInterface $from, \DateTimeInterface $to)
 * @method static Builder<static>|YachtAvailabilityBlock query()
 * @method static Builder<static>|YachtAvailabilityBlock whereCreatedAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereCreatedBy($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereDeletedAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereEndsAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereExpiresAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereId($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereNote($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereSourceId($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereSourceType($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereStartsAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereType($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereUpdatedAt($value)
 * @method static Builder<static>|YachtAvailabilityBlock whereYachtId($value)
 * @method static Builder<static>|YachtAvailabilityBlock withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|YachtAvailabilityBlock withoutTrashed()
 *
 * @mixin \Eloquent
 */
class YachtAvailabilityBlock extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtAvailabilityBlockFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return MorphTo<Model, $this> */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function hasLapsed(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Blocks that actually occupy the yacht right now — a lapsed option hold
     * does not.
     *
     * @param  Builder<YachtAvailabilityBlock>  $query
     * @return Builder<YachtAvailabilityBlock>
     */
    public function scopeEffective(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * @param  Builder<YachtAvailabilityBlock>  $query
     * @return Builder<YachtAvailabilityBlock>
     */
    public function scopeOverlapping(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->where('starts_at', '<', $to)->where('ends_at', '>', $from);
    }
}
