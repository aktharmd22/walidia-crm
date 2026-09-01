<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

/**
 * Nobody boards on an unverified identity. The check reads the
 * manifest, which is where the verification is recorded.
 */
class GuestsIdentityVerifiedCheck implements GateCheck
{
    public function key(): string
    {
        return 'guests.identity_verified';
    }

    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Booking) {
            return false;
        }

        $guests = $subject->guests();

        if ($guests->count() === 0) {
            return false;
        }

        if ((bool) ($params['allow_lead_only'] ?? false)) {
            return (clone $guests)->where('is_lead_guest', true)->where('id_verified', true)->exists();
        }

        return (clone $guests)->where('id_verified', false)->doesntExist();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        if (! $subject instanceof Booking) {
            return 'Guest ID verification is incomplete.';
        }

        $outstanding = $subject->guests()->where('id_verified', false)->count();

        return $subject->guests()->count() === 0
            ? 'No guests are listed on the manifest yet.'
            : sprintf('%d guest%s still unverified.', $outstanding, $outstanding === 1 ? '' : 's');
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Booking ? [
            'label' => 'Open Charter Day',
            'url' => route('charter.bookings.show', $subject->getKey()),
        ] : null;
    }
}
