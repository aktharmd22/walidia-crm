<?php

declare(strict_types=1);

namespace App\Domain\Operations\Actions;

use App\Domain\Gates\GateEvaluator;
use App\Domain\Gates\GateResult;
use App\Models\CrewAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Sending crew to a charter.
 *
 * Two conditions, both hard: Finance has released the booking, and nobody being
 * sent has an expired document. The second is not bureaucracy — an expired
 * seaman's book is a vessel detained at the marina gate.
 */
class DispatchCrew
{
    public function __construct(private readonly GateEvaluator $gates) {}

    public function execute(CrewAssignment $assignment, User $user, ?string $overrideReason = null): GateResult
    {
        $result = $overrideReason !== null
            ? $this->gates->override($assignment, 'crew-assignments.dispatch', $user, $overrideReason)
            : $this->gates->assertAction($assignment, 'crew-assignments.dispatch', $user);

        DB::transaction(function () use ($assignment, $user): void {
            $assignment->forceFill([
                'status' => 'confirmed',
                'dispatched_at' => now(),
                'dispatched_by' => $user->id,
            ])->save();

            $assignment->booking?->logActivity(
                'system',
                "Crew dispatched: {$assignment->crew?->full_name}",
                $assignment->role,
            );
        });

        return $result;
    }

    public function preview(CrewAssignment $assignment, User $user): GateResult
    {
        return $this->gates->forAction($assignment, 'crew-assignments.dispatch', $user);
    }
}
