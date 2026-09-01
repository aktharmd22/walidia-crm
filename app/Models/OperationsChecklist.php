<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\OperationsChecklistFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The pre-departure checklist for one charter.
 *
 * @property string $status
 * @property-read Collection<int, ChecklistItem> $items
 */
class OperationsChecklist extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<OperationsChecklistFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'checklist';
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return HasMany<ChecklistItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order');
    }

    public function blockingItemsOutstanding(): bool
    {
        return $this->items()->where('is_blocking', true)->where('status', '!=', 'done')->exists();
    }
}
