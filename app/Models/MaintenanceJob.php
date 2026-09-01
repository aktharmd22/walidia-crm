<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\MaintenanceJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Work on a managed yacht — routine, repair or refit. A job that blocks charter says so.
 */
class MaintenanceJob extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<MaintenanceJobFactory> */
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
        'due_on' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'owner_approved_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'owner_approval_required' => 'boolean',
        'blocks_charter' => 'boolean',
    ];

    public function sequenceKey(): string
    {
        return 'maintenance';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<ManagementAgreement, $this> */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ManagementAgreement::class, 'management_agreement_id');
    }

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_on !== null && $this->due_on->isPast() && $this->completed_at === null;
    }
}
