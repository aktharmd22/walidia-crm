<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtCharterProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Charter commerce, kept off the hull record so specs and pricing evolve apart.
 *
 * @property int $id
 * @property int $yacht_id
 * @property numeric|null $hourly_rate
 * @property numeric|null $half_day_rate
 * @property numeric|null $full_day_rate
 * @property numeric|null $overnight_rate
 * @property numeric|null $weekly_rate
 * @property numeric $peak_multiplier
 * @property string $currency
 * @property int $min_hours
 * @property numeric|null $apa_percentage
 * @property array<array-key, mixed>|null $included_extras
 * @property int|null $cancellation_policy_id
 * @property bool $is_bookable
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtCharterProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereApaPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCancellationPolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereFullDayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereHalfDayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereIncludedExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereIsBookable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereMinHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereOvernightRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile wherePeakMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereWeeklyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereYachtId($value)
 *
 * @mixin \Eloquent
 */
class YachtCharterProfile extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtCharterProfileFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'half_day_rate' => 'decimal:2',
            'full_day_rate' => 'decimal:2',
            'overnight_rate' => 'decimal:2',
            'weekly_rate' => 'decimal:2',
            'peak_multiplier' => 'decimal:2',
            'apa_percentage' => 'decimal:2',
            'included_extras' => 'array',
            'is_bookable' => 'boolean',
        ];
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
