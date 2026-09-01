<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Who may do what to a booking.
 *
 * The gate engine decides whether a transition is *possible*; this decides
 * whether this person is the one who gets to try. Both must pass.
 */
class BookingPolicy extends ResourcePolicy
{
    protected string $prefix = 'bookings';

    /** Operations sees every confirmed booking — they have to deliver them. */
    public function view(User $user, Model $model): bool
    {
        if (! $user->can('bookings.view')) {
            return false;
        }

        return $this->owns($user, $model) || $user->can('records.view-all');
    }

    /** A completed booking is history; a closed cost sheet freezes it entirely. */
    public function update(User $user, Model $model): bool
    {
        if (! $model instanceof Booking) {
            return false;
        }

        if ($model->status === 'completed' && ! $user->can('settings.manage')) {
            return false;
        }

        if ($model->costSheet?->isClosed() === true && ! $user->can('settings.manage')) {
            return false;
        }

        return parent::update($user, $model);
    }

    public function generateContract(User $user, Booking $booking): bool
    {
        return $user->can('bookings.generate-contract') && $this->owns($user, $booking);
    }

    /**
     * Finance grants Operational Release. Not Sales, however keen they are, and
     * not Operations, however ready they are.
     */
    public function releaseOperations(User $user, Booking $booking): bool
    {
        return $user->can('bookings.release-operations');
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $user->can('bookings.confirm') && $this->owns($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->can('bookings.cancel')
            && $this->owns($user, $booking)
            && ! in_array($booking->status, ['completed', 'cancelled'], true);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->can('bookings.complete') && $booking->status !== 'cancelled';
    }

    public function board(User $user, Booking $booking): bool
    {
        return $user->can('bookings.board');
    }
}
