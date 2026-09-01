<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;

/**
 * The manifest is what the marina and the authorities read; fewer
 * names than booked guests is a problem someone must resolve.
 */
class ManifestCompleteCheck implements GateCheck
{
    public function key(): string
    {
        return 'manifest.complete';
    }

    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Booking) {
            return false;
        }

        $expected = (int) $subject->guests_adults + (int) $subject->guests_children;

        return $expected > 0 && $subject->guests()->count() >= $expected;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        if (! $subject instanceof Booking) {
            return 'The guest manifest is incomplete.';
        }

        $expected = (int) $subject->guests_adults + (int) $subject->guests_children;

        return sprintf('Manifest incomplete: %d of %d guests listed.', $subject->guests()->count(), $expected);
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Booking ? [
            'label' => 'Open manifest',
            'url' => route('charter.bookings.show', $subject->getKey()),
        ] : null;
    }
}
