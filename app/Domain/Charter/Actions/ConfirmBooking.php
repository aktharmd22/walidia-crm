<?php

declare(strict_types=1);

namespace App\Domain\Charter\Actions;

use App\Domain\Gates\Exceptions\GateBlockedException;
use App\Domain\Gates\GateEvaluator;
use App\Domain\Gates\GateResult;
use App\Models\Booking;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Contract generation, confirmation and cancellation.
 *
 * Each is a guarded transition, so each goes through the evaluator: contract
 * generation needs verified KYC, and confirmation carries the soft gates —
 * a missing itinerary does not stop the charter, but somebody is told.
 */
class ConfirmBooking
{
    public function __construct(private readonly GateEvaluator $gates) {}

    /** Generating the contract requires verified KYC — a hard gate. */
    public function generateContract(Booking $booking, User $user, ?string $overrideReason = null): GateResult
    {
        $result = $overrideReason !== null
            ? $this->gates->override($booking, 'bookings.generate-contract', $user, $overrideReason)
            : $this->gates->assertAction($booking, 'bookings.generate-contract', $user);

        DB::transaction(function () use ($booking): void {
            $booking->forceFill(['status' => 'pending_contract'])->save();
            $booking->logActivity('system', 'Charter agreement generated');
        });

        return $result;
    }

    public function signContract(Booking $booking, User $user): void
    {
        DB::transaction(function () use ($booking, $user): void {
            $booking->forceFill([
                'contract_signed_at' => now(),
                'status' => 'deposit_pending',
            ])->save();

            $booking->logActivity('status_change', 'Charter agreement signed', "Recorded by {$user->name}");
        });
    }

    /**
     * Confirming is where the soft gates speak up: the charter proceeds, and
     * the warnings become tasks for whoever has to close them.
     */
    /**
     * Instantiate every active charter template against a booking, unless it
     * already carries one — confirming twice must not double the crew's work.
     */
    private function openChecklists(Booking $booking): void
    {
        ChecklistTemplate::query()
            ->where('business_line', 'charter')
            ->where('is_active', true)
            ->with('items')
            ->get()
            ->each(function (ChecklistTemplate $template) use ($booking): void {
                $exists = $booking->checklists()
                    ->where('checklist_template_id', $template->getKey())
                    ->exists();

                if (! $exists) {
                    $template->applyTo($booking);
                }
            });
    }

    public function confirm(Booking $booking, User $user): GateResult
    {
        $result = $this->gates->forTransition($booking, 'status', 'confirmed', $user);

        if ($result->blocked()) {
            throw new GateBlockedException($result);
        }

        DB::transaction(function () use ($booking): void {
            $booking->forceFill(['status' => 'confirmed'])->save();
            $booking->logActivity('status_change', 'Booking confirmed');

            // Operations is notified by the work appearing, not by an email:
            // confirming a booking stamps the standing templates onto it, so
            // the planning steps exist from the moment the charter is real.
            $this->openChecklists($booking);
        });

        return $result;
    }

    /**
     * Cancellation prices itself from the policy tier that applies on the day,
     * rather than leaving someone to work it out in a spreadsheet.
     */
    public function cancel(Booking $booking, User $user, string $reason): void
    {
        DB::transaction(function () use ($booking, $reason): void {
            $daysBefore = (int) now()->diffInDays($booking->starts_at, false);
            $policy = $booking->cancellationPolicy;
            $feePct = $policy?->feePercentageFor(max($daysBefore, 0)) ?? 0.0;
            $total = (float) ($booking->costSheet?->total_offer ?? 0);

            $booking->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancellation_fee' => round($total * $feePct / 100, 2),
            ])->save();

            // The yacht goes back on the market the moment the charter dies.
            $booking->yacht->availabilityBlocks()
                ->where('source_type', $booking->getMorphClass())
                ->where('source_id', $booking->getKey())
                ->delete();

            $booking->logActivity(
                'status_change',
                'Booking cancelled',
                sprintf('%s · cancellation fee %.2f%% (%d days before departure)', $reason, $feePct, $daysBefore),
            );
        });
    }
}
