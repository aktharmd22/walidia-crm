<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Money leaving the company: the seller, the co-broker, the referrer,
 * the vendor, the crew.
 *
 * A deal is not closed while any of this is outstanding — which is why the
 * deal-close gate reads this table.
 */
class Payout extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PayoutFactory> */
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
        'amount' => 'decimal:2',
        'amount_aed' => 'decimal:2',
        'due_on' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'payout';
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<Deal, $this> */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function payeeClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'payee_client_id');
    }

    /** @return BelongsTo<Vendor, $this> */
    public function payeeVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'payee_vendor_id');
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_on !== null && $this->due_on->isPast() && $this->paid_at === null;
    }
}
