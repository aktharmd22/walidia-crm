<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentScheduleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The payment plan behind a booking: deposit, balance, APA.
 *
 * @property string $currency
 * @property-read Collection<int, PaymentScheduleItem> $items
 */
class PaymentSchedule extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PaymentScheduleFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return HasMany<PaymentScheduleItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentScheduleItem::class);
    }
}
