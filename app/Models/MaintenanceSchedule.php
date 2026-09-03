<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonInterface;
use Database\Factories\MaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Preventive maintenance that recurs.
 *
 * Whichever comes first — the calendar or the engine hours — decides when it
 * is next due, because an engine that has run 400 hours in three months is
 * not on the same schedule as one that has run forty.
 */
class MaintenanceSchedule extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<MaintenanceScheduleFactory> */
    use HasFactory;

    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_done_on' => 'date',
        'next_due_on' => 'date',
        'budget_cost' => 'decimal:2',
        'blocks_charter' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Roll the schedule forward from the work just completed.
     */
    public function markDone(CarbonInterface $on, ?int $engineHours = null): void
    {
        $this->forceFill([
            'last_done_on' => $on,
            'last_done_engine_hours' => $engineHours ?? $this->last_done_engine_hours,
            'next_due_on' => $this->interval_days === null ? null : $on->copy()->addDays($this->interval_days),
        ])->save();
    }

    public function isDue(): bool
    {
        return $this->next_due_on !== null && ! $this->next_due_on->isFuture();
    }
}
