<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\ValuationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What the yacht is worth, and how we arrived at it.
 *
 * A listing price nobody can defend is how a mandate is lost at the first
 * offer, so the comparables sit beside the number.
 */
class Valuation extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ValuationFactory> */
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
        'valued_on' => 'date',
        'market_low' => 'decimal:2',
        'market_high' => 'decimal:2',
        'broker_valuation' => 'decimal:2',
        'recommended_asking' => 'decimal:2',
        'agreed_asking' => 'decimal:2',
        'comparables' => 'array',
    ];

    public function sequenceKey(): string
    {
        return 'valuation';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<Listing, $this> */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<User, $this> */
    public function valuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valued_by');
    }

    /** The spread a seller is really choosing within. */
    public function marketSpread(): ?float
    {
        if ($this->market_low === null || $this->market_high === null) {
            return null;
        }

        return round((float) $this->market_high - (float) $this->market_low, 2);
    }
}
