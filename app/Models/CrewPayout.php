<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use Database\Factories\CrewPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Per-charter pay and tips. Payroll proper is out of scope (Q13).
 *
 * @property string $status
 * @property numeric $net
 */
class CrewPayout extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CrewPayoutFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'days' => 'decimal:2',
        'day_rate' => 'decimal:2',
        'tips_amount' => 'decimal:2',
        'gross' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'payout';
    }

    /** @return BelongsTo<Crew, $this> */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class, 'crew_id');
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
