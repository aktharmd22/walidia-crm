<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

/**
 * Nothing operational — crew, vendors, dispatch — happens before
 * Finance has released the booking.
 */
class OperationalReleaseCheck implements GateCheck
{
    public function key(): string
    {
        return 'booking.operational_release';
    }

    public function passes(Model $subject, array $params): bool
    {
        $booking = $subject instanceof Booking ? $subject : ($subject->booking ?? null);

        return $booking instanceof Booking && $booking->operational_release_at !== null;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        return 'Operational Release has not been granted for this booking.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        $booking = $subject instanceof Booking ? $subject : ($subject->booking ?? null);

        return $booking instanceof Booking ? [
            'label' => 'Open booking',
            'url' => route('charter.bookings.show', $booking->getKey()),
        ] : null;
    }
}
