<?php

declare(strict_types=1);

namespace App\Domain\Charter\Actions;

use App\Domain\Gates\GateEvaluator;
use App\Domain\Gates\GateResult;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Operational Release — the hinge of the whole charter operation.
 *
 * Before it, nothing operational may happen: no crew dispatched, no vendor
 * booked, no guest boarded. It is granted by Finance, only against a cleared
 * deposit, and it is the gate everything downstream reads.
 *
 * The action cannot be constructed without the evaluator, which is how "there
 * is no second code path" is enforced rather than merely intended (D-004).
 */
class ReleaseOperations
{
    public function __construct(private readonly GateEvaluator $gates) {}

    /**
     * @param  string|null  $overrideReason  Admin override; recorded in the register.
     */
    public function execute(Booking $booking, User $user, ?string $overrideReason = null): GateResult
    {
        if ($booking->isReleased()) {
            return GateResult::pass();
        }

        $result = $overrideReason !== null
            ? $this->gates->override($booking, 'bookings.release-operations', $user, $overrideReason)
            : $this->gates->assertAction($booking, 'bookings.release-operations', $user);

        DB::transaction(function () use ($booking, $user, $overrideReason): void {
            $booking->forceFill([
                'operational_release_at' => now(),
                'operational_release_by' => $user->id,
                'status' => in_array($booking->status, ['draft', 'pending_contract'], true)
                    ? $booking->status
                    : 'confirmed',
            ])->save();

            $booking->logActivity(
                'status_change',
                'Operational Release granted',
                $overrideReason === null
                    ? 'Deposit confirmed cleared by Finance.'
                    : "Granted by override: {$overrideReason}",
            );
        });

        return $result;
    }

    /** Dry run for the screen: why is this button disabled? */
    public function preview(Booking $booking, User $user): GateResult
    {
        return $this->gates->forAction($booking, 'bookings.release-operations', $user);
    }
}
