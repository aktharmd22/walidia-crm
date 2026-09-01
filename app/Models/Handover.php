<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\HandoverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Keys, documents, inventory, flag and insurance. A sale is not finished until every one of them has moved.
 */
class Handover extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<HandoverFactory> */
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
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'keys_handed_over' => 'boolean',
        'documents_handed_over' => 'boolean',
        'inventory_signed' => 'boolean',
        'flag_registration_updated' => 'boolean',
        'insurance_transferred' => 'boolean',
    ];

    public function sequenceKey(): string
    {
        return 'handover';
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return BelongsTo<Marina, $this> */
    public function marina(): BelongsTo
    {
        return $this->belongsTo(Marina::class);
    }

    /** Every box, not most of them. */
    public function isComplete(): bool
    {
        return $this->keys_handed_over
            && $this->documents_handed_over
            && $this->inventory_signed
            && $this->flag_registration_updated
            && $this->insurance_transferred;
    }
}
