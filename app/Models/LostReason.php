<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\LostReasonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * "Closed Lost" without a reason is unreportable, so the reason is a record.
 *
 * @property int $id
 * @property int|null $pipeline_id
 * @property string $label
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Pipeline|null $pipeline
 *
 * @method static \Database\Factories\LostReasonFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason wherePipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason withoutTrashed()
 *
 * @mixin \Eloquent
 */
class LostReason extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<LostReasonFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Pipeline, $this> */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }
}
