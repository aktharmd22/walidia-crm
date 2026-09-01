<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A yacht for sale, and the mandate that lets us sell it.
 *
 * The mandate's expiry is a soft gate: selling under a lapsed agreement is
 * how a brokerage loses a commission it has already earned.
 */
class Listing extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ListingFactory> */
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
        'asking_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'agreement_signed_on' => 'date',
        'agreement_expires_on' => 'date',
        'listed_on' => 'date',
        'requires_proof_of_funds' => 'boolean',
        'requires_nda' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function sequenceKey(): string
    {
        return 'listing';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<YachtOwner, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(YachtOwner::class, 'yacht_owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<Nda, $this> */
    public function ndas(): HasMany
    {
        return $this->hasMany(Nda::class);
    }

    /** @return HasMany<Viewing, $this> */
    public function viewings(): HasMany
    {
        return $this->hasMany(Viewing::class);
    }

    /** @return HasMany<Offer, $this> */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /** @return HasMany<Survey, $this> */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function agreementIsActive(): bool
    {
        return $this->agreement_expires_on === null || ! $this->agreement_expires_on->isPast();
    }

    public function agreementExpiresWithin(int $days): bool
    {
        return $this->agreement_expires_on !== null
            && $this->agreement_expires_on->isBetween(now(), now()->addDays($days));
    }

    /**
     * @param  Builder<Listing>  $query
     * @return Builder<Listing>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('reference', 'like', $like)
                ->orWhereHas('yacht', fn (Builder $yacht) => $yacht->where('name', 'like', $like));
        });
    }
}
