<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PipelineStageFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $pipeline_id
 * @property string $key
 * @property string $name
 * @property string|null $name_ar
 * @property int $sort_order
 * @property string $colour_token
 * @property int $probability
 * @property bool $is_won
 * @property bool $is_lost
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Deal> $deals
 * @property-read int|null $deals_count
 * @property-read Pipeline $pipeline
 *
 * @method static \Database\Factories\PipelineStageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereColourToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereIsLost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereIsWon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage wherePipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereProbability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PipelineStage extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PipelineStageFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    /** @return BelongsTo<Pipeline, $this> */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
