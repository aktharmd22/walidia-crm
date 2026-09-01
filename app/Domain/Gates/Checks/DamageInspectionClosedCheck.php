<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\DamageReport;
use App\Models\SecurityDeposit;
use Illuminate\Database\Eloquent\Model;

/**
 * The security deposit is not released while an inspection is open —
 * which is precisely when someone is most tempted to release it.
 */
class DamageInspectionClosedCheck implements GateCheck
{
    public function key(): string
    {
        return 'damage.inspection_closed';
    }

    public function passes(Model $subject, array $params): bool
    {
        $bookingId = $this->bookingId($subject);

        if ($bookingId === null) {
            return true;
        }

        return DamageReport::query()
            ->where('booking_id', $bookingId)
            ->where('inspection_status', '!=', 'closed')
            ->doesntExist();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        $bookingId = $this->bookingId($subject);

        $open = $bookingId === null ? 0 : DamageReport::query()
            ->where('booking_id', $bookingId)
            ->where('inspection_status', '!=', 'closed')
            ->count();

        return $open === 1
            ? 'A damage inspection is still open.'
            : "{$open} damage inspections are still open.";
    }

    public function resolution(Model $subject, array $params): ?array
    {
        $bookingId = $this->bookingId($subject);

        return $bookingId === null ? null : [
            'label' => 'Open damage reports',
            'url' => route('charter.damage-reports.index', ['booking_id' => $bookingId]),
        ];
    }

    private function bookingId(Model $subject): ?int
    {
        if ($subject instanceof Booking) {
            return (int) $subject->getKey();
        }

        if ($subject instanceof SecurityDeposit) {
            return (int) $subject->booking_id;
        }

        $bookingId = $subject->getAttribute('booking_id');

        return $bookingId === null ? null : (int) $bookingId;
    }
}
