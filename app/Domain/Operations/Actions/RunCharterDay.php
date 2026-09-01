<?php

declare(strict_types=1);

namespace App\Domain\Operations\Actions;

use App\Domain\Charter\CostSheetCalculator;
use App\Domain\Gates\GateEvaluator;
use App\Domain\Gates\GateResult;
use App\Models\Booking;
use App\Models\CharterDayLog;
use App\Models\CharterExtra;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The charter day itself.
 *
 * Written from a phone on a dock, so every write here is small, idempotent
 * where it can be, and appended rather than edited — an operations log that can
 * be rewritten afterwards is not a log.
 */
class RunCharterDay
{
    public function __construct(private readonly GateEvaluator $gates) {}

    /**
     * Boarding: IDs verified and the safety briefing logged. Both hard, because
     * both are what an inspection asks about afterwards.
     */
    public function board(Booking $booking, User $user, ?string $overrideReason = null): GateResult
    {
        $result = $overrideReason !== null
            ? $this->gates->override($booking, 'bookings.board', $user, $overrideReason)
            : $this->gates->assertAction($booking, 'bookings.board', $user);

        DB::transaction(function () use ($booking, $user): void {
            $booking->forceFill(['boarded_at' => now(), 'status' => 'in_progress'])->save();

            $this->log($booking, 'guest_boarded', $user, 'Guests boarded', [
                'guests' => $booking->guestCount(),
            ]);
        });

        return $result;
    }

    public function depart(Booking $booking, User $user, ?string $location = null): CharterDayLog
    {
        return $this->log($booking, 'departure', $user, 'Departed', [], $location);
    }

    public function arrive(Booking $booking, User $user, ?string $location = null): CharterDayLog
    {
        $log = $this->log($booking, 'arrival', $user, 'Returned to berth', [], $location);

        $booking->forceFill(['status' => 'completed', 'completed_at' => now()])->save();

        return $log;
    }

    /**
     * A guest asks for something on the day. It becomes a priced extra rather
     * than a note somebody has to remember at invoicing time.
     */
    public function recordExtra(Booking $booking, User $user, string $description, float $quantity, float $unitPrice): CharterExtra
    {
        return DB::transaction(function () use ($booking, $user, $description, $quantity, $unitPrice): CharterExtra {
            $extra = CharterExtra::create([
                'booking_id' => $booking->getKey(),
                'source' => 'guest_request',
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'status' => 'requested',
                'requested_by' => $user->id,
            ]);

            $this->log($booking, 'request', $user, "Guest request: {$description}", [
                'amount' => $extra->amount,
            ]);

            return $extra;
        });
    }

    /**
     * Approving an extra pushes it onto the cost sheet, so what was agreed on
     * the water is what appears on the invoice.
     */
    public function approveExtra(CharterExtra $extra, User $user): void
    {
        DB::transaction(function () use ($extra, $user): void {
            $sheet = $extra->booking?->costSheet;

            $line = $sheet?->lines()->create([
                'phase' => 'actual',
                'section' => 'revenue',
                'category' => 'other_revenue',
                'description' => $extra->description,
                'quantity' => $extra->quantity,
                'unit_price' => $extra->unit_price,
                'amount' => $extra->amount,
            ]);

            $extra->forceFill([
                'status' => 'approved',
                'approved_by' => $user->id,
                'cost_sheet_line_id' => $line?->getKey(),
            ])->save();

            if ($sheet !== null) {
                app(CostSheetCalculator::class)->recalculate($sheet->fresh());
            }
        });
    }

    /** Completing a checklist item, with whatever evidence it demands. */
    public function completeChecklistItem(ChecklistItem $item, User $user, ?string $note = null, ?string $photoPath = null): void
    {
        $item->forceFill([
            'status' => 'done',
            'completed_at' => now(),
            'completed_by' => $user->id,
            'note' => $note ?? $item->note,
            'photo_path' => $photoPath ?? $item->photo_path,
        ])->save();

        $checklist = $item->checklist;

        if ($checklist === null) {
            return;
        }

        $total = $checklist->items()->count();
        $done = $checklist->items()->where('status', 'done')->count();

        $checklist->forceFill([
            'completion_pct' => $total === 0 ? 0 : (int) round($done / $total * 100),
            'status' => $done === $total ? 'complete' : 'open',
            'completed_at' => $done === $total ? now() : null,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(Booking $booking, string $type, User $user, string $body, array $meta = [], ?string $location = null): CharterDayLog
    {
        return CharterDayLog::create([
            'booking_id' => $booking->getKey(),
            'event_type' => $type,
            'occurred_at' => now(),
            'logged_by' => $user->id,
            'location' => $location,
            'body' => $body,
            'meta' => $meta === [] ? null : $meta,
            'synced_at' => now(),
        ]);
    }
}
