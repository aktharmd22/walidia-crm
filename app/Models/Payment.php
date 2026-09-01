<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Money in.
 *
 * `cleared_at` is the field that matters: Operational Release and ownership
 * transfer both read cleared money, never money that has merely been promised
 * or shown as a screenshot of a transfer.
 *
 * @property CarbonImmutable|null $received_at
 * @property CarbonImmutable|null $cleared_at
 * @property CarbonImmutable|null $reconciled_at
 * @property string $status
 * @property string $currency
 * @property string|null $reference
 * @property numeric $amount
 * @property numeric $exchange_rate
 * @property int|null $client_id
 * @property-read Receipt|null $receipt
 * @property-read Collection<int, PaymentAllocation> $allocations
 */
class Payment extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_aed' => 'decimal:2',
        'bank_charge_amount' => 'decimal:2',
        'bank_charge_vat' => 'decimal:2',
        'received_at' => 'datetime',
        'cleared_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'receipt';
    }

    protected static function booted(): void
    {
        // Everything reports in AED; the rate is captured at the moment the
        // money arrives, not looked up later (D-002).
        static::saving(function (Payment $payment): void {
            $payment->amount_aed = round((float) $payment->amount * (float) $payment->exchange_rate, 2);
        });
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /** @return HasOne<Receipt, $this> */
    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared' && $this->cleared_at !== null;
    }

    public function unallocatedAmount(): float
    {
        return round((float) $this->amount - (float) $this->allocations()->sum('amount'), 2);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'cleared' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded', 'partially_refunded' => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function scopeCleared(Builder $query): Builder
    {
        return $query->where('status', 'cleared')->whereNotNull('cleared_at');
    }

    /**
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('reference', 'like', $like)
                ->orWhere('gateway_reference', 'like', $like)
                ->orWhereHas('client', fn (Builder $client) => $client->where('full_name', 'like', $like));
        });
    }
}
