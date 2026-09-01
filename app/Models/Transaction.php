<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The sale itself: contract, escrow, AML, and the transfer of ownership.
 *
 * Ownership does not move until the money has cleared and AML is clear. That
 * is a hard gate, and it is the one that protects the brokerage's licence.
 */
class Transaction extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<TransactionFactory> */
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
        'agreed_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'deposit_cleared_at' => 'datetime',
        'balance_cleared_at' => 'datetime',
        'contract_signed_on' => 'date',
        'expected_closing_on' => 'date',
        'aml_cleared' => 'boolean',
        'aml_cleared_at' => 'datetime',
        'ownership_transferred_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'transaction';
    }

    /** @return BelongsTo<Listing, $this> */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<Offer, $this> */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'buyer_client_id');
    }

    /** @return BelongsTo<YachtOwner, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(YachtOwner::class, 'seller_owner_id');
    }

    public function fundsCleared(): bool
    {
        return $this->balance_cleared_at !== null;
    }

    public function isTransferred(): bool
    {
        return $this->ownership_transferred_at !== null;
    }

    /** @return HasOne<Handover, $this> */
    public function handover(): HasOne
    {
        return $this->hasOne(Handover::class);
    }

    /** @return HasOne<Deal, $this> */
    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }
}
