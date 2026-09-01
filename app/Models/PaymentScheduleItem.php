<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentScheduleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * One instalment: deposit, balance, APA. The deposit row is what the
 * Operational Release gate reads.
 */
class PaymentScheduleItem extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PaymentScheduleItemFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'amount' => 'decimal:2',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PaymentSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_schedule_item_id');
    }

    /** Cleared money only. */
    public function clearedAmount(): float
    {
        return (float) $this->allocations()
            ->whereHas('payment', fn ($query) => $query->where('status', 'cleared'))
            ->sum('amount');
    }

    public function isSettled(): bool
    {
        return $this->status === 'paid' || $this->clearedAmount() >= (float) $this->amount;
    }

    public function isOverdue(): bool
    {
        return ! $this->isSettled() && $this->due_at !== null && $this->due_at->isPast();
    }
}
