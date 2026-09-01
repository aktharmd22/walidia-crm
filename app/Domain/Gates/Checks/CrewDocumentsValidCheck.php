<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Crew;
use App\Models\CrewAssignment;
use Illuminate\Database\Eloquent\Model;

/**
 * Crew paperwork.
 *
 * With within_days = 0 this is the hard stop on dispatch: an expired document
 * means that person cannot be sent. With a window, it is the 30-day warning.
 */
class CrewDocumentsValidCheck implements GateCheck
{
    public function key(): string
    {
        return 'crew.documents_valid';
    }

    public function passes(Model $subject, array $params): bool
    {
        $window = (int) ($params['within_days'] ?? 0);

        foreach ($this->crewFor($subject) as $crew) {
            if ($crew->hasExpiredDocuments()) {
                return false;
            }

            if ($window > 0 && $crew->documentsExpiringWithin($window) > 0) {
                return false;
            }
        }

        return true;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        $window = (int) ($params['within_days'] ?? 0);
        $names = [];

        foreach ($this->crewFor($subject) as $crew) {
            if ($crew->hasExpiredDocuments()) {
                $names[] = "{$crew->full_name} (expired)";
            } elseif ($window > 0 && $crew->documentsExpiringWithin($window) > 0) {
                $names[] = "{$crew->full_name} (expiring)";
            }
        }

        return $names === []
            ? 'Crew documentation is not valid.'
            : 'Crew documentation problem: '.implode(', ', $names).'.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return ['label' => 'Open crew expiry', 'url' => route('crew.expiry')];
    }

    /**
     * @return list<Crew>
     */
    private function crewFor(Model $subject): array
    {
        if ($subject instanceof Crew) {
            return [$subject];
        }

        if ($subject instanceof CrewAssignment) {
            $crew = $subject->crew;

            return $crew instanceof Crew ? [$crew] : [];
        }

        // A booking: everyone assigned to it.
        $bookingId = $subject->getAttribute('booking_id') ?? $subject->getKey();

        return CrewAssignment::query()
            ->where('booking_id', $bookingId)
            ->with('crew')
            ->get()
            ->map(fn (CrewAssignment $assignment): ?Crew => $assignment->crew)
            ->filter()
            ->values()
            ->all();
    }
}
