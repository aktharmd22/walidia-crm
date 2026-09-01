<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Carbon\CarbonImmutable;
use Database\Factories\CharterEnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What the client actually asked for: dates, guests, budget, marinas, extras.
 *
 * Everything downstream — matching, proposals, the booking — reads this.
 */
#[ScopedBy([ScopedToOwner::class])]

/**
 * @property CarbonImmutable|null $requested_date
 * @property string|null $start_time
 * @property string|null $reference
 * @property string $status
 * @property string $currency
 * @property numeric|null $duration_hours
 * @property numeric|null $budget_min
 * @property numeric|null $budget_max
 * @property int $guests_adults
 * @property int $guests_children
 * @property int|null $client_id
 * @property int|null $company_id
 * @property int|null $deal_id
 * @property int|null $pickup_marina_id
 * @property int|null $dropoff_marina_id
 * @property int|null $yacht_preference_id
 * @property int|null $assigned_user_id
 * @property string|null $itinerary_notes
 * @property-read Marina|null $pickupMarina
 * @property-read Client|null $client
 */
class CharterEnquiry extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CharterEnquiryFactory> */
    use HasFactory;

    /** @use HasOwnerScope<CharterEnquiry> */
    use HasOwnerScope;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requested_date' => 'date',
        'alternative_dates' => 'array',
        'requested_extras' => 'array',
        'duration_hours' => 'decimal:2',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function sequenceKey(): string
    {
        return 'enquiry';
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Marina, $this> */
    public function pickupMarina(): BelongsTo
    {
        return $this->belongsTo(Marina::class, 'pickup_marina_id');
    }

    /** @return BelongsTo<Marina, $this> */
    public function dropoffMarina(): BelongsTo
    {
        return $this->belongsTo(Marina::class, 'dropoff_marina_id');
    }

    /** @return HasMany<CharterMatch, $this> */
    public function matches(): HasMany
    {
        return $this->hasMany(CharterMatch::class)->orderByDesc('score');
    }

    /** @return HasMany<CharterProposal, $this> */
    public function proposals(): HasMany
    {
        return $this->hasMany(CharterProposal::class)->orderByDesc('version');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function guestCount(): int
    {
        return (int) $this->guests_adults + (int) $this->guests_children;
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'won' => 'success',
            'proposed' => 'attention',
            'matching' => 'warning',
            'lost', 'cancelled' => 'danger',
            default => 'info',
        };
    }

    /**
     * @param  Builder<CharterEnquiry>  $query
     * @return Builder<CharterEnquiry>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('reference', 'like', $like)
                ->orWhere('experience_type', 'like', $like)
                ->orWhereHas('client', fn (Builder $client) => $client->where('full_name', 'like', $like));
        });
    }
}
