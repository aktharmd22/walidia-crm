<?php

declare(strict_types=1);

namespace App\Domain\Charter\Actions;

use App\Domain\Gates\GateEvaluator;
use App\Models\Booking;
use App\Models\CharterEnquiry;
use App\Models\CharterProposal;
use App\Models\PaymentSchedule;
use App\Models\User;
use App\Models\YachtAvailabilityBlock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Acceptance is the moment a conversation becomes a commitment.
 *
 * It locks the yacht (an availability block, the one table that owns fleet
 * occupancy), opens the booking, and lays down the payment schedule whose
 * deposit row the Operational Release gate will later read.
 */
class AcceptProposal
{
    public function __construct(private readonly GateEvaluator $gates) {}

    public function execute(CharterProposal $proposal, User $user, ?string $overrideReason = null): Booking
    {
        if (! $proposal->isAcceptable() && $overrideReason === null) {
            throw ValidationException::withMessages([
                'proposal' => $proposal->hasExpired()
                    ? 'This proposal has expired. Issue a new version before accepting it.'
                    : 'Only a sent proposal can be accepted.',
            ]);
        }

        $enquiry = $proposal->enquiry;
        $item = $proposal->items()->where('type', 'charter')->first();
        $yachtId = $item?->yacht_id ?? $enquiry->yacht_preference_id;

        if ($yachtId === null) {
            throw ValidationException::withMessages([
                'proposal' => 'This proposal has no yacht on it, so there is nothing to book.',
            ]);
        }

        return DB::transaction(function () use ($proposal, $enquiry, $yachtId, $user): Booking {
            $proposal->forceFill(['status' => 'accepted', 'responded_at' => now()])->save();

            // Every other version is superseded — one accepted price, on file.
            $enquiry->proposals()
                ->whereKeyNot($proposal->getKey())
                ->whereIn('status', ['draft', 'sent', 'viewed'])
                ->update(['status' => 'expired']);

            $starts = $this->departureInstant($enquiry);
            $ends = $starts->addMinutes((int) round((float) ($enquiry->duration_hours ?? 4) * 60));

            $booking = Booking::create([
                'charter_proposal_id' => $proposal->getKey(),
                'charter_enquiry_id' => $enquiry->getKey(),
                'client_id' => $enquiry->client_id,
                'company_id' => $enquiry->company_id,
                'yacht_id' => $yachtId,
                'deal_id' => $enquiry->deal_id,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'departure_marina_id' => $enquiry->pickup_marina_id,
                'return_marina_id' => $enquiry->dropoff_marina_id ?? $enquiry->pickup_marina_id,
                'guests_adults' => $enquiry->guests_adults,
                'guests_children' => $enquiry->guests_children,
                'itinerary' => $enquiry->itinerary_notes,
                'currency' => $proposal->currency,
                'status' => 'pending_contract',
                'assigned_user_id' => $enquiry->assigned_user_id ?? $user->id,
            ]);

            // The lock the availability gate exists to protect.
            YachtAvailabilityBlock::create([
                'yacht_id' => $yachtId,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'type' => 'booking',
                'source_type' => $booking->getMorphClass(),
                'source_id' => $booking->getKey(),
                'note' => "Booking {$booking->reference}",
                'created_by' => $user->id,
            ]);

            $this->buildPaymentSchedule($booking, (float) $proposal->total);

            $enquiry->forceFill(['status' => 'won'])->save();

            $proposal->logActivity('status_change', 'Proposal accepted');
            $booking->logActivity('system', "Booking opened from proposal {$proposal->reference}");

            return $booking;
        });
    }

    /**
     * The departure instant, derived from the pickup marina's timezone rather
     * than assumed to be Dubai (D-010).
     */
    private function departureInstant(CharterEnquiry $enquiry): CarbonImmutable
    {
        $timezone = $enquiry->pickupMarina?->timezone ?? config('walidia.display_timezone');
        $date = $enquiry->requested_date?->toDateString() ?? now()->addWeek()->toDateString();
        $time = $enquiry->start_time ?? '10:00:00';

        return CarbonImmutable::parse("{$date} {$time}", $timezone)->setTimezone('UTC');
    }

    /** Deposit, then balance — the plan the release gate reads. */
    private function buildPaymentSchedule(Booking $booking, float $total): void
    {
        $depositPct = (float) config('walidia.charter.deposit_percentage', 50);
        $balanceDays = (int) config('walidia.charter.balance_due_days_before', 7);

        $deposit = round($total * $depositPct / 100, 2);
        $balance = round($total - $deposit, 2);

        $schedule = PaymentSchedule::create([
            'booking_id' => $booking->getKey(),
            'name' => 'Charter payment plan',
            'total_amount' => $total,
            'currency' => $booking->currency,
            'status' => 'open',
        ]);

        $schedule->items()->createMany([
            [
                'sequence' => 1,
                'label' => 'deposit',
                'percentage' => $depositPct,
                'amount' => $deposit,
                'due_at' => now()->addDays(3),
                'status' => 'due',
            ],
            [
                'sequence' => 2,
                'label' => 'balance',
                'percentage' => 100 - $depositPct,
                'amount' => $balance,
                'due_at' => $booking->starts_at->subDays($balanceDays),
                'status' => 'pending',
            ],
        ]);
    }
}
