<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\CrewPayout;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

/**
 * A charter does not close with somebody still owed money — crew, tips
 * or a vendor invoice sitting unpaid.
 */
class PayoutsIssuedCheck implements GateCheck
{
    public function key(): string
    {
        return 'payouts.issued';
    }

    public function passes(Model $subject, array $params): bool
    {
        $bookingId = $this->bookingId($subject);

        if ($bookingId === null) {
            return true;
        }

        $crewOutstanding = CrewPayout::query()
            ->where('booking_id', $bookingId)
            ->where('status', '!=', 'paid')
            ->exists();

        $vendorOutstanding = PurchaseOrder::query()
            ->where('booking_id', $bookingId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->exists();

        return ! $crewOutstanding && ! $vendorOutstanding;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        $bookingId = $this->bookingId($subject);

        if ($bookingId === null) {
            return 'Not all payouts have been issued.';
        }

        $crew = CrewPayout::where('booking_id', $bookingId)->where('status', '!=', 'paid')->count();
        $vendors = PurchaseOrder::where('booking_id', $bookingId)->whereNotIn('status', ['paid', 'cancelled'])->count();

        $parts = [];

        if ($crew > 0) {
            $parts[] = "{$crew} crew payout".($crew === 1 ? '' : 's');
        }

        if ($vendors > 0) {
            $parts[] = "{$vendors} purchase order".($vendors === 1 ? '' : 's');
        }

        return 'Still outstanding: '.implode(' and ', $parts).'.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return ['label' => 'Open crew payouts', 'url' => route('crew.payouts.index')];
    }

    private function bookingId(Model $subject): ?int
    {
        if ($subject instanceof Booking) {
            return (int) $subject->getKey();
        }

        $bookingId = $subject->getAttribute('booking_id');

        return $bookingId === null ? null : (int) $bookingId;
    }
}
