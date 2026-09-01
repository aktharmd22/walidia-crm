<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Booking;
use App\Models\Yacht;

/**
 * Every operations form asks the same two questions first — which charter, and
 * which yacht — so the option lists live in one place rather than three.
 */
trait PicksOperationsContext
{
    /**
     * Only charters that have actually happened or are about to: an incident on
     * an unconfirmed enquiry is not a thing.
     *
     * @return list<array{value: int, label: string}>
     */
    protected function bookingOptions(): array
    {
        return Booking::query()
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->latest('starts_at')
            ->limit(200)
            ->get(['id', 'reference', 'starts_at'])
            ->map(fn (Booking $booking): array => [
                'value' => $booking->id,
                'label' => sprintf('%s · %s', $booking->reference, $booking->starts_at->format('d M Y')),
            ])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function yachtOptions(): array
    {
        return Yacht::orderBy('name')->get(['id', 'name'])
            ->map(fn (Yacht $yacht): array => ['value' => $yacht->id, 'label' => (string) $yacht->name])
            ->all();
    }
}
