<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BerthFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $marina_id
 * @property string $code
 * @property numeric|null $max_loa_m
 * @property numeric|null $monthly_fee
 * @property string|null $notes
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Marina|null $marina
 *
 * @method static \Database\Factories\BerthFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMaxLoaM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMonthlyFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Berth extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<BerthFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'max_loa_m' => 'decimal:2', 'monthly_fee' => 'decimal:2'];
    }

    /** @return BelongsTo<Marina, $this> */
    public function marina(): BelongsTo
    {
        return $this->belongsTo(Marina::class);
    }
}
