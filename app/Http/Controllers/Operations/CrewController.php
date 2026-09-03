<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Domain\Gates\GateEvaluator;
use App\Domain\Operations\Actions\DispatchCrew;
use App\Http\Controllers\Concerns\IssuesPortalLinks;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\CrewResource;
use App\Models\Booking;
use App\Models\Crew;
use App\Models\CrewAssignment;
use App\Models\CrewDocument;
use App\Models\CrewPayout;
use App\Models\Marina;
use App\Support\Paginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Crew, their documents, and who is going where.
 *
 * Document expiry is not an administrative detail: an expired seaman's book is
 * a vessel held at the marina gate, which is why it is a hard gate on dispatch.
 *
 * @extends ResourceController<Crew>
 */
class CrewController extends ResourceController
{
    use IssuesPortalLinks;

    protected string $model = Crew::class;

    protected string $name = 'crew';

    protected string $pages = 'Crew';

    protected string $resource = CrewResource::class;

    protected array $indexWith = ['documents'];

    protected array $showWith = ['documents', 'assignments.booking', 'payouts'];

    protected array $sortable = ['full_name', 'role', 'status', 'created_at'];

    protected string $defaultSort = 'full_name';

    protected array $filterable = ['role', 'status', 'employment_type'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Crew::class);

        $crew = Crew::create($this->validated($request));

        return redirect()->route('crew.show', $crew)->with('success', "{$crew->full_name} added.");
    }

    public function update(Request $request, Crew $crew): RedirectResponse
    {
        $this->authorize('update', $crew);

        $crew->update($this->validated($request));

        return back()->with('success', 'Crew member updated.');
    }

    /** The screen that stops a charter being held up at the gate. */
    public function expiry(Request $request): Response
    {
        $this->authorize('viewAny', Crew::class);

        $days = (int) $request->integer('days', 60);

        $documents = CrewDocument::query()
            ->with('crew:id,full_name,role')
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', now()->addDays($days))
            ->orderBy('expires_on')
            ->get();

        return Inertia::render('Crew/Expiry', [
            'days' => $days,
            'rows' => $documents->map(fn (CrewDocument $document): array => [
                'id' => $document->id,
                'crew' => $document->crew?->full_name,
                'crew_id' => $document->crew_id,
                'role' => $document->crew?->role,
                'type' => $document->type,
                'expires_on' => $document->expires_on?->toDateString(),
                'is_expired' => $document->isExpired(),
                'tone' => $document->isExpired() ? 'danger' : 'warning',
            ]),
        ]);
    }

    public function assignments(Request $request): Response
    {
        $this->authorize('viewAny', CrewAssignment::class);

        $gates = app(GateEvaluator::class);

        $assignments = CrewAssignment::query()
            ->with(['crew:id,full_name,role', 'booking:id,reference,starts_at,operational_release_at'])
            ->whereDate('starts_at', '>=', now()->subWeek())
            ->orderBy('starts_at')
            ->paginate(50);

        return Inertia::render('Crew/Assignments', [
            'rows' => Paginate::shape($assignments->through(fn (CrewAssignment $assignment): array => [
                'id' => $assignment->id,
                'crew' => $assignment->crew?->full_name,
                'role' => $assignment->role,
                'booking' => $assignment->booking?->reference,
                'starts_at' => $assignment->starts_at->toIso8601String(),
                'status' => $assignment->status,
                'dispatched_at' => $assignment->dispatched_at?->toIso8601String(),
                // The screen asks the engine why dispatch is blocked, and
                // shows the answer rather than a grey button.
                'gate' => $gates->forAction($assignment, 'crew-assignments.dispatch', $request->user())->toArray(),
            ])),
            'can' => ['dispatch' => $request->user()->can('crew-assignments.dispatch')],
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $this->authorize('create', CrewAssignment::class);

        $data = $request->validate([
            'crew_id' => ['required', 'integer', 'exists:crew,id'],
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'role' => ['nullable', 'string', 'max:48'],
            'day_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        CrewAssignment::create($data + [
            'assignable_type' => $booking->getMorphClass(),
            'assignable_id' => $booking->getKey(),
            'starts_at' => $booking->starts_at,
            'ends_at' => $booking->ends_at,
            'status' => 'proposed',
        ]);

        return back()->with('success', 'Crew assigned. Dispatch is blocked until Operational Release.');
    }

    public function dispatch(Request $request, CrewAssignment $assignment, DispatchCrew $action): RedirectResponse
    {
        $this->authorize('dispatch', $assignment);

        $reason = $request->filled('override_reason') && $request->user()->can('gates.override')
            ? (string) $request->input('override_reason')
            : null;

        $action->execute($assignment, $request->user(), $reason);

        return back()->with('success', 'Crew dispatched.');
    }

    public function storeDocument(Request $request, Crew $crew): RedirectResponse
    {
        $this->authorize('update', $crew);

        $data = $request->validate([
            'type' => ['required', 'string', 'max:48'],
            'number' => ['nullable', 'string', 'max:64'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after:today'],
        ]);

        $crew->documents()->create($data + ['status' => 'valid']);

        return back()->with('success', 'Document recorded.');
    }

    public function payouts(Request $request): Response
    {
        $this->authorize('viewAny', CrewPayout::class);

        return Inertia::render('Crew/Payouts', [
            'rows' => Paginate::shape(CrewPayout::query()
                ->with(['crew:id,full_name', 'booking:id,reference'])
                ->latest()
                ->paginate(50)
                ->through(fn (CrewPayout $payout): array => [
                    'id' => $payout->id,
                    'reference' => $payout->reference,
                    'crew' => $payout->crew?->full_name,
                    'booking' => $payout->booking?->reference,
                    'days' => $payout->days,
                    'tips_amount' => $payout->tips_amount,
                    'net' => $payout->net,
                    'currency' => $payout->currency,
                    'status' => $payout->status,
                ])),
        ]);
    }

    protected function baseQuery(Request $request): Builder
    {
        return Crew::query()->withCount('documents');
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'marinas' => Marina::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (Marina $marina): array => ['value' => $marina->id, 'label' => $marina->name]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:90'],
            'last_name' => ['nullable', 'string', 'max:90'],
            'role' => ['required', Rule::in(['captain', 'engineer', 'deckhand', 'steward', 'chef', 'other'])],
            'employment_type' => ['required', Rule::in(['employee', 'freelance'])],
            'nationality' => ['nullable', 'string', 'max:90'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'day_rate' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', Rule::in(config('walidia.currencies'))],
            'home_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'primary_yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'on_leave', 'inactive'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Crew $record */
        return [
            'documents' => $record->documents->map(fn (CrewDocument $document): array => [
                'id' => $document->id,
                'type' => $document->type,
                'expires_on' => $document->expires_on?->toDateString(),
                'is_expired' => $document->isExpired(),
                'is_expiring' => $document->isExpiring(30),
            ]),
        ];
    }

    /**
     * The dispatch sheet a crew member opens on their phone at the marina:
     * where to be, when, on which yacht. No guests, no client, no money.
     */
    public function shareAssignment(Request $request, CrewAssignment $assignment): RedirectResponse
    {
        $this->authorize('view', $assignment);

        $assignment->booking?->logActivity('system', 'Dispatch sheet sent to crew');

        return $this->issuePortalLink($assignment, 'crew.assignment', 'portal.assignment');
    }
}
