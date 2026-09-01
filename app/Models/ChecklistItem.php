<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * One line of an operations checklist. A blocking item — the safety briefing,
 * for instance — is read directly by the boarding gate.
 *
 * @property string $key
 * @property string $status
 * @property bool $is_blocking
 */
class ChecklistItem extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ChecklistItemFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_blocking' => 'boolean',
    ];

    /** @return BelongsTo<OperationsChecklist, $this> */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OperationsChecklist::class, 'operations_checklist_id');
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }
}
