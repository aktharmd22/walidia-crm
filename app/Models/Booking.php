<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Carbon\CarbonImmutable;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A charter, from contract to completion.
 *
 * `status` is the booking's own lifecycle; the deal's `stage` is the board
 * position (D-005). `operational_release_at` is the pivot the entire operations
 * side gates on — no crew is dispatched and no vendor is booked before Finance
 * sets it.
 */
#[ScopedBy([ScopedToOwner::class])]

/**
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property CarbonImmutable|null $contract_signed_at
 * @property CarbonImmutable|null $operational_release_at
 * @property CarbonImmutable|null $boarded_at
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property string $status
 * @property string $currency
 * @property string|null $reference
 * @property string|null $itinerary
 * @property int $guests_adults
 * @property int $guests_children
 * @property int|null $client_id
 * @property int|null $company_id
 * @property int|null $deal_id
 * @property int|null $assigned_user_id
 * @property-read Client|null $client
 * @property-read Yacht|null $yacht
 * @property-read Marina|null $departureMarina
 * @property-read CharterEnquiry|null $enquiry
 * @property-read CharterProposal|null $proposal
 * @property-read CostSheet|null $costSheet
 * @property-read PaymentSchedule|null $paymentSchedule
 * @property-read CancellationPolicy|null $cancellationPolicy
 */
class Booking extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /** @use HasOwnerScope<Booking> */
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
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'contract_signed_at' => 'datetime',
        'operational_release_at' => 'datetime',
        'boarded_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancellation_fee' => 'decimal:2',
        'apa_amount' => 'decimal:2',
    ];

    public function sequenceKey(): string
    {
        return 'booking';
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<CharterEnquiry, $this> */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(CharterEnquiry::class, 'charter_enquiry_id');
    }

    /** @return BelongsTo<CharterProposal, $this> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CharterProposal::class, 'charter_proposal_id');
    }

    /** @return BelongsTo<Marina, $this> */
    public function departureMarina(): BelongsTo
    {
        return $this->belongsTo(Marina::class, 'departure_marina_id');
    }

    /** @return BelongsTo<Marina, $this> */
    public function returnMarina(): BelongsTo
    {
        return $this->belongsTo(Marina::class, 'return_marina_id');
    }

    /** @return BelongsTo<CancellationPolicy, $this> */
    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    /** @return HasMany<BookingGuest, $this> */
    public function guests(): HasMany
    {
        return $this->hasMany(BookingGuest::class);
    }

    /** @return HasOne<CostSheet, $this> */
    public function costSheet(): HasOne
    {
        return $this->hasOne(CostSheet::class);
    }

    /** @return HasOne<PaymentSchedule, $this> */
    public function paymentSchedule(): HasOne
    {
        return $this->hasOne(PaymentSchedule::class);
    }

    /** @return HasMany<GuestManifest, $this> */
    public function manifests(): HasMany
    {
        return $this->hasMany(GuestManifest::class);
    }

    /** @return HasMany<CharterDayLog, $this> */
    public function dayLogs(): HasMany
    {
        return $this->hasMany(CharterDayLog::class)->orderByDesc('occurred_at');
    }

    /** @return HasMany<CharterExtra, $this> */
    public function extras(): HasMany
    {
        return $this->hasMany(CharterExtra::class);
    }

    /** @return HasMany<CrewAssignment, $this> */
    public function crewAssignments(): HasMany
    {
        return $this->hasMany(CrewAssignment::class);
    }

    /** @return HasMany<DamageReport, $this> */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    /** @return HasMany<Incident, $this> */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /** @return HasMany<OperationsChecklist, $this> */
    public function checklists(): HasMany
    {
        return $this->hasMany(OperationsChecklist::class);
    }

    /** @return HasOne<SecurityDeposit, $this> */
    public function securityDeposit(): HasOne
    {
        return $this->hasOne(SecurityDeposit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /* ── state ──────────────────────────────────────────────────────────── */

    public function isReleased(): bool
    {
        return $this->operational_release_at !== null;
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function guestCount(): int
    {
        return (int) $this->guests_adults + (int) $this->guests_children;
    }

    public function durationHours(): float
    {
        return round($this->starts_at->diffInMinutes($this->ends_at) / 60, 2);
    }

    /**
     * Departure in the marina's own local time. Everything is stored UTC, and
     * a charter leaving Malé is not leaving on Dubai time (D-010).
     */
    public function departureLocal(): CarbonImmutable
    {
        $timezone = $this->departureMarina?->timezone ?? config('walidia.display_timezone');

        return $this->starts_at->setTimezone($timezone);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'confirmed', 'in_progress' => 'info',
            'deposit_pending', 'pending_contract' => 'warning',
            'contract_signed' => 'attention',
            'cancelled', 'no_show' => 'danger',
            default => 'neutral',
        };
    }

    /* ── scopes ─────────────────────────────────────────────────────────── */

    /**
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeUpcoming(Builder $query, int $days = 14): Builder
    {
        return $query->whereIn('status', ['confirmed', 'in_progress', 'deposit_pending', 'contract_signed'])
            ->whereBetween('starts_at', [now(), now()->addDays($days)])
            ->orderBy('starts_at');
    }

    /**
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('reference', 'like', $like)
                ->orWhereHas('client', fn (Builder $client) => $client->where('full_name', 'like', $like))
                ->orWhereHas('yacht', fn (Builder $yacht) => $yacht->where('name', 'like', $like));
        });
    }
}
