<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\LeadSourceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property string $name
 * @property string $channel
 * @property string|null $utm_key
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Lead> $leads
 * @property-read int|null $leads_count
 *
 * @method static \Database\Factories\LeadSourceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereUtmKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource withoutTrashed()
 *
 * @mixin \Eloquent
 */
class LeadSource extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<LeadSourceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'source_id');
    }
}
