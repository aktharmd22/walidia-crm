<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Database\Factories\WorkflowRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * One firing of one rule against one record.
 *
 * Recorded whether it sent, skipped or failed: an automation nobody can audit
 * is one nobody trusts.
 */
class WorkflowRun extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<WorkflowRunFactory> */
    use HasFactory;

    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'due_at' => 'datetime',
        'ran_at' => 'datetime',
    ];

    /** @return BelongsTo<WorkflowRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(WorkflowRule::class, 'workflow_rule_id');
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isDue(): bool
    {
        return $this->status === 'pending' && ! $this->due_at->isFuture();
    }
}
