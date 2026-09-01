<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

/**
 * A deal does not close with money received and no receipt issued —
 * that is the gap an audit finds first.
 */
class ReceiptsGeneratedCheck implements GateCheck
{
    public function key(): string
    {
        return 'receipts.generated';
    }

    public function passes(Model $subject, array $params): bool
    {
        $bookingId = $this->bookingId($subject);

        if ($bookingId === null) {
            return true;
        }

        return Payment::query()
            ->where('status', 'cleared')
            ->whereHas('allocations.invoice', fn ($query) => $query
                ->where('subject_type', 'booking')
                ->where('subject_id', $bookingId))
            ->whereDoesntHave('receipt')
            ->doesntExist();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        return 'Not every cleared payment has a receipt.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return ['label' => 'Open payments', 'url' => route('finance.payments.index')];
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
