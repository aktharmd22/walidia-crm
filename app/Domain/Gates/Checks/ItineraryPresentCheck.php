<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

/**
 * A soft gate: a confirmed charter with no itinerary is not blocked,
 * but somebody should be told.
 */
class ItineraryPresentCheck implements GateCheck
{
    public function key(): string
    {
        return 'itinerary.present';
    }

    public function passes(Model $subject, array $params): bool
    {
        return $subject instanceof Booking && filled($subject->itinerary);
    }

    public function failureMessage(Model $subject, array $params): string
    {
        return 'No itinerary has been added to this booking.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Booking ? [
            'label' => 'Add itinerary',
            'url' => route('charter.bookings.show', $subject->getKey()),
        ] : null;
    }
}
