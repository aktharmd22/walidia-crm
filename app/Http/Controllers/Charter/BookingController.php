<?php

declare(strict_types=1);

namespace App\Http\Controllers\Charter;

use App\Domain\Charter\Actions\ConfirmBooking;
use App\Domain\Charter\Actions\ReleaseOperations;
use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Support\Statuses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Bookings, and the transitions the gate engine guards.
 *
 * Every state change here is a named action rather than a PATCH of `status`,
 * because each one is evaluated before it happens — and a blocked one comes
 * back with the exact conditions that stopped it.
 *
 * @extends ResourceController<Booking>
 */
class BookingController extends ResourceController
{
    protected string $model = Booking::class;

    protected string $name = 'bookings';

    protected string $pages = 'Charter/Bookings';

    protected string $resource = BookingResource::class;

    protected ?string $routePrefix = 'charter.bookings';

    protected array $indexWith = ['client:id,full_name', 'yacht:id,name', 'assignee:id,name', 'departureMarina:id,name,timezone'];

    protected array $showWith = ['client', 'yacht', 'assignee', 'departureMarina', 'returnMarina', 'guests', 'costSheet.lines', 'paymentSchedule.items', 'securityDeposit'];

    protected array $sortable = ['reference', 'starts_at', 'status', 'created_at'];

    protected string $defaultSort = 'starts_at';

    protected array $filterable = ['status', 'yacht_id', 'assigned_user_id'];

    public function calendar(Request $request): Response
    {
        $this->authorize('viewAny', Booking::class);

        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->addMonth()->endOfMonth();

        return Inertia::render('Charter/Calendar', [
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'bookings' => BookingResource::collection(
                Booking::query()
                    ->with($this->indexWith)
                    ->whereBetween('starts_at', [$from, $to])
                    ->orderBy('starts_at')
                    ->get(),
            )->resolve(),
        ]);
    }

    /* ── guarded transitions ────────────────────────────────────────────── */

    public function generateContract(Request $request, Booking $booking, ConfirmBooking $action): RedirectResponse
    {
        $this->authorize('generateContract', $booking);

        $action->generateContract($booking, $request->user(), $this->overrideReason($request));

        return back()->with('success', 'Charter agreement generated.');
    }

    public function signContract(Request $request, Booking $booking, ConfirmBooking $action): RedirectResponse
    {
        $this->authorize('update', $booking);

        $action->signContract($booking, $request->user());

        return back()->with('success', 'Contract recorded as signed.');
    }

    /**
     * Operational Release. Finance only, against a cleared deposit — the hinge
     * the whole operations side turns on.
     */
    public function releaseOperations(Request $request, Booking $booking, ReleaseOperations $action): RedirectResponse
    {
        $this->authorize('releaseOperations', $booking);

        $action->execute($booking, $request->user(), $this->overrideReason($request));

        return back()->with('success', 'Operational Release granted. Crew and vendors can now be dispatched.');
    }

    public function confirm(Request $request, Booking $booking, ConfirmBooking $action): RedirectResponse
    {
        $this->authorize('confirm', $booking);

        $result = $action->confirm($booking, $request->user());

        return back()->with(
            $result->hasWarnings() ? 'warning' : 'success',
            $result->hasWarnings()
                ? 'Confirmed, with warnings: '.$result->summary()
                : 'Booking confirmed.',
        );
    }

    public function cancel(Request $request, Booking $booking, ConfirmBooking $action): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate(['reason' => ['required', 'string', 'max:190']]);

        $action->cancel($booking, $request->user(), $data['reason']);

        return back()->with('success', 'Booking cancelled and the yacht released back to the calendar.');
    }

    public function complete(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('complete', $booking);

        $booking->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $booking->logActivity('status_change', 'Charter completed');

        return back()->with('success', 'Charter marked complete.');
    }

    /* ── writes ─────────────────────────────────────────────────────────── */

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        $booking->update($request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'departure_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'return_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'guests_adults' => ['required', 'integer', 'min:0', 'max:500'],
            'guests_children' => ['required', 'integer', 'min:0', 'max:500'],
            'itinerary' => ['nullable', 'string', 'max:5000'],
            'special_requests' => ['nullable', 'string', 'max:5000'],
            'cancellation_policy_id' => ['nullable', 'integer', 'exists:cancellation_policies,id'],
            'apa_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(Statuses::keys(Statuses::BOOKING))],
        ]));

        return back()->with('success', 'Booking updated.');
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    protected function baseQuery(Request $request): Builder
    {
        return Booking::query();
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Booking $record */
        $gates = app(GateEvaluator::class);
        $user = $request->user();

        return [
            'timeline' => ActivityResource::collection(
                $record->activities()->with('user:id,name')->limit(25)->get(),
            )->resolve(),

            // The screen asks the engine why a button is disabled, rather than
            // guessing — and gets the same answer the write path would give.
            'gates' => [
                'release' => $gates->forAction($record, 'bookings.release-operations', $user)->toArray(),
                'contract' => $gates->forAction($record, 'bookings.generate-contract', $user)->toArray(),
                'board' => $gates->forAction($record, 'bookings.board', $user)->toArray(),
            ],

            /*
             * A booking with no payment schedule must still send an array. The
             * null-safe operator returns null, and a React default parameter
             * only fills in for undefined — so null reached the page and
             * schedule.length threw on render.
             */
            'schedule' => $record->paymentSchedule?->items->map(fn ($item): array => [
                'id' => $item->id,
                'label' => $item->label,
                'amount' => $item->amount,
                'due_at' => $item->due_at?->toIso8601String(),
                'status' => $item->status,
                'cleared' => $item->clearedAmount(),
                'overdue' => $item->isOverdue(),
            ])->values() ?? [],

            'can' => array_merge($this->recordAbilities($request, $record), [
                'release' => $user->can('releaseOperations', $record),
                'confirm' => $user->can('confirm', $record),
                'cancel' => $user->can('cancel', $record),
                'override' => $user->can('gates.override'),
            ]),
        ];
    }

    /** An override is only honoured from a user who may make one. */
    private function overrideReason(Request $request): ?string
    {
        if (! $request->filled('override_reason')) {
            return null;
        }

        abort_unless($request->user()->can('gates.override'), 403);

        return (string) $request->input('override_reason');
    }
}
