<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Concerns\PicksOperationsContext;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Incidents are recorded, never quietly resolved: an open incident is visible
 * on the booking, on the yacht, and in the operations queue until it is closed
 * with a written outcome.
 *
 * @extends ResourceController<Incident>
 */
class IncidentController extends ResourceController
{
    use PicksOperationsContext;

    protected string $model = Incident::class;

    protected string $name = 'incidents';

    protected string $pages = 'Charter/Incidents';

    protected string $resource = IncidentResource::class;

    protected ?string $routePrefix = 'charter.incidents';

    protected array $indexWith = ['booking:id,reference', 'yacht:id,name'];

    protected array $showWith = ['booking.client', 'yacht'];

    protected array $sortable = ['reference', 'occurred_at', 'severity', 'status'];

    protected string $defaultSort = '-occurred_at';

    protected array $filterable = ['status', 'severity', 'type'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Incident::class);

        $incident = Incident::create($this->validated($request) + [
            'reported_by' => $request->user()->id,
            'status' => 'open',
        ]);

        return redirect()->route('charter.incidents.show', $incident)
            ->with('success', "Incident {$incident->reference} recorded.");
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $incident->update($this->validated($request));

        return back()->with('success', 'Incident updated.');
    }

    public function close(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('close', $incident);

        $data = $request->validate(['resolution' => ['required', 'string', 'max:2000']]);

        $incident->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
            'immediate_action' => $incident->immediate_action ?? $data['resolution'],
        ])->save();

        $incident->booking?->logActivity('system', "Incident {$incident->reference} closed", $data['resolution']);

        return back()->with('success', 'Incident closed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'bookings' => $this->bookingOptions(),
            'yachts' => $this->yachtOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'type' => ['required', 'string', 'max:48'],
            'severity' => ['required', Rule::in(['minor', 'moderate', 'major', 'critical'])],
            'occurred_at' => ['required', 'date'],
            'description' => ['required', 'string', 'max:5000'],
            'immediate_action' => ['nullable', 'string', 'max:5000'],
            'injuries' => ['boolean'],
            'authorities_notified' => ['boolean'],
            'insurance_claim_ref' => ['nullable', 'string', 'max:90'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        return [
            'can' => $this->recordAbilities($request, $record) + [
                'close' => $request->user()->can('close', $record),
            ],
        ];
    }
}
