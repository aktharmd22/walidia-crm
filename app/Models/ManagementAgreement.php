<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\ManagementAgreementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What we have agreed to do with someone else's yacht, for how long, and for what fee.
 */
class ManagementAgreement extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ManagementAgreementFactory> */
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
        'monthly_fee' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'opex_budget_annual' => 'decimal:2',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function sequenceKey(): string
    {
        return 'management';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<YachtOwner, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(YachtOwner::class, 'yacht_owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<OwnerStatement, $this> */
    public function statements(): HasMany
    {
        return $this->hasMany(OwnerStatement::class);
    }

    /** @return HasMany<MaintenanceJob, $this> */
    public function maintenanceJobs(): HasMany
    {
        return $this->hasMany(MaintenanceJob::class);
    }

    public function isExpiring(int $days = 90): bool
    {
        return $this->ends_on !== null && $this->ends_on->isBetween(now(), now()->addDays($days));
    }
}
