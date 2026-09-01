<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PipelineFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $name_ar
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Deal> $deals
 * @property-read int|null $deals_count
 * @property-read Collection<int, PipelineStage> $stages
 * @property-read int|null $stages_count
 *
 * @method static \Database\Factories\PipelineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Pipeline extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PipelineFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<PipelineStage, $this> */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
