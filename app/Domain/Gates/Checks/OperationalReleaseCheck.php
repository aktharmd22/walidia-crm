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
        $booking = $this->bookingFor($subject);

        return $booking !== null && $booking->operational_release_at !== null;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        return 'Operational Release has not been granted for this booking.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        $booking = $this->bookingFor($subject);

        return $booking !== null ? [
            'label' => 'Open booking',
            'url' => route('charter.bookings.show', $booking->getKey()),
        ] : null;
    }

    private function bookingFor(Model $subject): ?Booking
    {
        if ($subject instanceof Booking) {
            return $subject;
        }

        $booking = $subject->getAttribute('booking');

        return $booking instanceof Booking ? $booking : null;
    }
}
