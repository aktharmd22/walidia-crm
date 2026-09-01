<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Which invoice, and which scheduled instalment, a payment settled.
 *
 * @property numeric $amount
 * @property-read Invoice|null $invoice
 * @property-read PaymentScheduleItem|null $scheduleItem
 */
class PaymentAllocation extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PaymentAllocationFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<PaymentScheduleItem, $this> */
    public function scheduleItem(): BelongsTo
    {
        return $this->belongsTo(PaymentScheduleItem::class, 'payment_schedule_item_id');
    }
}
