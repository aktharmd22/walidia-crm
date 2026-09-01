<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\PaymentScheduleItem;
use Illuminate\Database\Eloquent\Model;

/**
 * Operational Release turns on this and nothing else: money that has
 * cleared, not money that has been promised.
 */
class DepositClearedCheck implements GateCheck
{
    public function key(): string
    {
        return 'payment.deposit_cleared';
    }

    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Booking) {
            return false;
        }

        $item = $this->depositItem($subject, $params);

        if ($item === null) {
            // No schedule means nothing to clear — that is a configuration
            // problem, and it should stop the release rather than wave it past.
            return false;
        }

        return $item->status === 'paid' || $this->clearedAgainst($item) >= (float) $item->amount;
    }

    public function failureMessage(Model $subject, array $params): string
    {
        if (! $subject instanceof Booking) {
            return 'Finance has not confirmed a cleared deposit.';
        }

        $item = $this->depositItem($subject, $params);

        if ($item === null) {
            return 'No payment schedule exists for this booking, so there is no deposit to confirm.';
        }

        $cleared = $this->clearedAgainst($item);
        $currency = $item->schedule?->currency ?? 'AED';

        return sprintf(
            'Deposit not cleared: %s %s of %s %s received.',
            $currency,
            number_format($cleared, 2),
            $currency,
            number_format((float) $item->amount, 2),
        );
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Booking ? [
            'label' => 'Open payment schedule',
            'url' => route('charter.bookings.show', $subject->getKey()),
        ] : null;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function depositItem(Booking $booking, array $params): ?PaymentScheduleItem
    {
        $label = (string) ($params['schedule_label'] ?? 'deposit');

        return PaymentScheduleItem::query()
            ->whereHas('schedule', fn ($query) => $query->where('booking_id', $booking->getKey()))
            ->where('label', $label)
            ->orderBy('sequence')
            ->first();
    }

    private function clearedAgainst(PaymentScheduleItem $item): float
    {
        return (float) $item->allocations()
            ->whereHas('payment', fn ($query) => $query->whereNotNull('cleared_at')->where('status', 'cleared'))
            ->sum('amount');
    }
}
