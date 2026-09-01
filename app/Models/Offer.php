<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * An offer on a listing. Submitting one requires proof of funds when the
 * seller's mandate demands it — which, at this end of the market, it does.
 */
class Offer extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<OfferFactory> */
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
        'deposit_amount' => 'decimal:2',
        'subject_to_survey' => 'boolean',
        'subject_to_sea_trial' => 'boolean',
        'proof_of_funds_received' => 'boolean',
        'valid_until' => 'date',
        'submitted_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'offer';
    }

    /** @return BelongsTo<Listing, $this> */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<Survey, $this> */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
