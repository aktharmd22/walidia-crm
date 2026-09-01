<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SecurityDepositFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Held against damage. It is not released until the damage inspection is
 * closed — that is a hard gate, and this is the record it acts on.
 */
class SecurityDeposit extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<SecurityDepositFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'released_amount' => 'decimal:2',
        'collected_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'released' => 'success',
            'partially_released' => 'warning',
            'forfeited' => 'danger',
            default => 'info',
        };
    }
}
