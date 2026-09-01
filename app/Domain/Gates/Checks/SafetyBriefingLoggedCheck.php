<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Model;

/**
 * Reads one named, blocking checklist item — the safety briefing at
 * boarding is the one this exists for.
 */
class SafetyBriefingLoggedCheck implements GateCheck
{
    public function key(): string
    {
        return 'checklist.item_complete';
    }

    public function passes(Model $subject, array $params): bool
    {
        $key = (string) ($params['item_key'] ?? '');

        if (! $subject instanceof Booking || $key === '') {
            return false;
        }

        return ChecklistItem::query()
            ->whereHas('checklist', fn ($query) => $query->where('booking_id', $subject->getKey()))
            ->where('key', $key)
            ->where('status', 'done')
            ->exists();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        $label = str_replace('_', ' ', (string) ($params['item_key'] ?? 'checklist item'));

        return ucfirst($label).' has not been logged.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Booking ? [
            'label' => 'Open checklist',
            'url' => route('charter.bookings.show', $subject->getKey()),
        ] : null;
    }
}
