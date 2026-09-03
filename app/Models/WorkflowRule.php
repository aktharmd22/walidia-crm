<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Carbon\CarbonInterface;
use Database\Factories\WorkflowRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * When to say it.
 *
 * An event or a schedule, an offset, an optional condition, an action. Rules
 * are data so an operations manager can move a reminder without a deployment.
 */
class WorkflowRule extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<WorkflowRuleFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'conditions' => 'array',
        'action_params' => 'array',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<MessageTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    /** @return HasMany<WorkflowRun, $this> */
    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }

    /**
     * When this rule should fire for a given anchor date.
     */
    public function dueAt(CarbonInterface $anchor): CarbonInterface
    {
        return $anchor->copy()->addHours($this->offset_hours);
    }
}
