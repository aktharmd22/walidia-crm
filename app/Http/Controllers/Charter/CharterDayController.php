<?php

declare(strict_types=1);

namespace App\Http\Controllers\Charter;

use App\Domain\Gates\GateEvaluator;
use App\Domain\Operations\Actions\RunCharterDay;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ChecklistItem;
use App\Models\DamageReport;
use App\Models\Incident;
use App\Models\OperationsChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Charter Day — designed for a phone on a dock, one-handed, in sunlight.
 *
 * Everything here is a short, single-purpose write: board, depart, log, request,
 * arrive. Nothing on this screen opens a modal on top of a modal, and nothing
 * needs a second screen to complete.
 */
class CharterDayController extends Controller
{
    /** Today's charters, for the operations manager to pick from. */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Booking::class);

        $timezone = config('walidia.display_timezone');
        $today = now($timezone)->startOfDay();

        $bookings = Booking::query()
            ->with(['client:id,full_name', 'yacht:id,name', 'departureMarina:id,name,timezone'])
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->whereBetween('starts_at', [$today->copy()->subDay(), $today->copy()->addDays(2)])
            ->orderBy('starts_at')
            ->get();

        return Inertia::render('Charter/Day/Index', [
            'bookings' => BookingResource::collection($bookings)->resolve(),
        ]);
    }

    public function show(Request $request, Booking $booking, GateEvaluator $gates): Response
    {
        $this->authorize('view', $booking);

        $booking->load(['client', 'yacht', 'departureMarina', 'guests']);

        $checklist = $booking->relationLoaded('checklists')
            ? null
            : OperationsChecklist::where('booking_id', $booking->id)->with('items')->first();

        return Inertia::render('Charter/Day/Show', [
            'record' => BookingResource::make($booking)->resolve(),
            'gate' => $gates->forAction($booking, 'bookings.board', $request->user())->toArray(),
            'guests' => $booking->guests->map(fn ($guest): array => [
                'id' => $guest->id,
                'name' => $guest->name,
                'nationality' => $guest->nationality,
                'is_lead_guest' => $guest->is_lead_guest,
                'id_verified' => $guest->id_verified,
                'checked_in_at' => $guest->checked_in_at?->toIso8601String(),
            ]),
            'checklist' => $checklist === null ? null : [
                'id' => $checklist->id,
                'completion_pct' => $checklist->completion_pct,
                'items' => $checklist->items->map(fn (ChecklistItem $item): array => [
                    'id' => $item->id,
                    'key' => $item->key,
                    'title' => $item->title,
                    'section' => $item->section,
                    'status' => $item->status,
                    'is_blocking' => $item->is_blocking,
                ]),
            ],
            'log' => $booking->dayLogs()
                ->with('logger:id,name')
                ->latest('occurred_at')
                ->limit(50)
                ->get()
                ->map(fn ($entry): array => [
                    'id' => $entry->id,
                    'type' => $entry->event_type,
                    'body' => $entry->body,
                    'occurred_at' => $entry->occurred_at->toIso8601String(),
                    'by' => $entry->logger?->name,
                ]),
            'extras' => $booking->extras()->latest()->get()->map(fn ($extra): array => [
                'id' => $extra->id,
                'description' => $extra->description,
                'amount' => $extra->amount,
                'status' => $extra->status,
            ]),
            'can' => [
                'board' => $request->user()->can('board', $booking),
                'override' => $request->user()->can('gates.override'),
            ],
        ]);
    }

    /* ── the day's writes ───────────────────────────────────────────────── */

    public function board(Request $request, Booking $booking, RunCharterDay $day): RedirectResponse
    {
        $this->authorize('board', $booking);

        $reason = $request->filled('override_reason') && $request->user()->can('gates.override')
            ? (string) $request->input('override_reason')
            : null;

        $day->board($booking, $request->user(), $reason);

        return back()->with('success', 'Guests boarded.');
    }

    public function verifyGuest(Request $request, Booking $booking, int $guestId): RedirectResponse
    {
        $this->authorize('board', $booking);

        $booking->guests()->whereKey($guestId)->update([
            'id_verified' => true,
            'id_verified_by' => $request->user()->id,
            'checked_in_at' => now(),
        ]);

        return back();
    }

    public function log(Request $request, Booking $booking, RunCharterDay $day): RedirectResponse
    {
        $this->authorize('view', $booking);

        $data = $request->validate([
            'event_type' => ['required', Rule::in(['departure', 'arrival', 'note', 'fuel', 'status_update'])],
            'body' => ['required', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:190'],
        ]);

        match ($data['event_type']) {
            'departure' => $day->depart($booking, $request->user(), $data['location'] ?? null),
            'arrival' => $day->arrive($booking, $request->user(), $data['location'] ?? null),
            default => $day->log($booking, $data['event_type'], $request->user(), $data['body'], [], $data['location'] ?? null),
        };

        return back()->with('success', 'Logged.');
    }

    public function storeExtra(Request $request, Booking $booking, RunCharterDay $day): RedirectResponse
    {
        $this->authorize('view', $booking);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:190'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $day->recordExtra($booking, $request->user(), $data['description'], (float) $data['quantity'], (float) $data['unit_price']);

        return back()->with('success', 'Request recorded — it will reach the invoice.');
    }

    public function completeChecklistItem(Request $request, Booking $booking, ChecklistItem $item, RunCharterDay $day): RedirectResponse
    {
        $this->authorize('view', $booking);

        $data = $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $day->completeChecklistItem($item, $request->user(), $data['note'] ?? null);

        return back();
    }

    public function reportIncident(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $data = $request->validate([
            'type' => ['required', 'string', 'max:48'],
            'severity' => ['required', Rule::in(['minor', 'moderate', 'major', 'critical'])],
            'description' => ['required', 'string', 'max:5000'],
            'injuries' => ['boolean'],
        ]);

        $incident = Incident::create($data + [
            'booking_id' => $booking->getKey(),
            'yacht_id' => $booking->yacht_id,
            'occurred_at' => now(),
            'reported_by' => $request->user()->id,
            'status' => 'open',
        ]);

        $booking->logActivity('system', "Incident reported: {$incident->reference}", $data['description']);

        return back()->with('warning', "Incident {$incident->reference} recorded and open.");
    }

    public function reportDamage(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'deduct_from_deposit' => ['boolean'],
        ]);

        $report = DamageReport::create($data + [
            'booking_id' => $booking->getKey(),
            'yacht_id' => $booking->yacht_id,
            'discovered_at' => now(),
            'discovered_by' => $request->user()->id,
            'inspection_status' => 'pending',
        ]);

        // The deposit gate now reads this: it stays held until the inspection
        // is closed.
        $booking->logActivity('system', "Damage reported: {$report->reference}", $data['description']);

        return back()->with('warning', 'Damage recorded. The security deposit is held until the inspection is closed.');
    }
}
