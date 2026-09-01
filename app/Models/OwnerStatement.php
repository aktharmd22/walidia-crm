<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\OwnerStatementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What the owner earned and what it cost, for one period.
 *
 * The numbers are computed from the charters and jobs in the period, never
 * typed in twice.
 */
class OwnerStatement extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<OwnerStatementFactory> */
    use HasFactory;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'charter_revenue' => 'decimal:2',
        'management_fee' => 'decimal:2',
        'operating_costs' => 'decimal:2',
        'maintenance_costs' => 'decimal:2',
        'crew_costs' => 'decimal:2',
        'net_to_owner' => 'decimal:2',
        'issued_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'statement';
    }

    /** @return BelongsTo<ManagementAgreement, $this> */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ManagementAgreement::class, 'management_agreement_id');
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** Revenue less every cost the owner carries. */
    public function recalculate(): void
    {
        $this->net_to_owner = round(
            (float) $this->charter_revenue
                - (float) $this->management_fee
                - (float) $this->operating_costs
                - (float) $this->maintenance_costs
                - (float) $this->crew_costs,
            2,
        );
    }
}
