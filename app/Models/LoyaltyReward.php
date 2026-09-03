<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\LoyaltyRewardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Gift vouchers, points and upgrades — the reason a client comes back rather than shops around.
 */
class LoyaltyReward extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<LoyaltyRewardFactory> */
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
        'value' => 'decimal:2',
        'valid_from' => 'date',
        'expires_on' => 'date',
        'redeemed_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'reward';
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isRedeemable(): bool
    {
        return $this->status === 'issued'
            && ($this->expires_on === null || ! $this->expires_on->isPast());
    }
}
