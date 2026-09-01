<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\ViewingResource;
use App\Models\Client;
use App\Models\Listing;
use App\Models\Marina;
use App\Models\Viewing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A buyer aboard someone else's yacht.
 *
 * Scheduling is a guarded transition: signed NDA, verified buyer, or it does
 * not happen — and the screen says which of the two is missing.
 *
 * @extends ResourceController<Viewing>
 */
class ViewingController extends ResourceController
{
    protected string $model = Viewing::class;

    protected string $name = 'viewings';

    protected string $pages = 'Brokerage/Viewings';

    protected string $resource = ViewingResource::class;

    protected ?string $routePrefix = 'brokerage.viewings';

    protected array $indexWith = ['client:id,full_name', 'listing:id,reference'];

    protected array $showWith = ['client', 'listing.yacht', 'assignee'];

    protected array $sortable = ['reference', 'scheduled_at', 'status'];

    protected string $defaultSort = '-scheduled_at';

    protected array $filterable = ['status', 'listing_id', 'client_id', 'assigned_user_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Viewing::class);

        $record = Viewing::create($this->validated($request));

        return redirect()->route('brokerage.viewings.show', $record)->with('success', 'Viewing created.');
    }

    public function update(Request $request, Viewing $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Viewing updated.');
    }

    /**
     * The guarded transition. The gate is evaluated by the engine, not here.
     */
    public function schedule(Request $request, Viewing $viewing, GateEvaluator $gates): RedirectResponse
    {
        $this->authorize('schedule', $viewing);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'override_reason' => ['nullable', 'string'],
        ]);

        $reason = ($data['override_reason'] ?? null) !== null && $request->user()->can('gates.override')
            ? (string) $data['override_reason']
            : null;

        $reason !== null
            ? $gates->override($viewing, 'viewings.schedule', $request->user(), $reason)
            : $gates->assertTransition($viewing, 'status', 'scheduled', $request->user());

        $viewing->forceFill([
            'scheduled_at' => $data['scheduled_at'],
            'marina_id' => $data['marina_id'] ?? $viewing->marina_id,
            'status' => 'scheduled',
        ])->save();

        $viewing->logActivity('status_change', 'Viewing scheduled');

        return back()->with('success', 'Viewing scheduled.');
    }

    public function complete(Request $request, Viewing $viewing): RedirectResponse
    {
        $this->authorize('update', $viewing);

        $data = $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
            'interest_level' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $viewing->forceFill($data + ['status' => 'completed', 'completed_at' => now()])->save();
        $viewing->logActivity('note', 'Viewing completed', $data['feedback']);

        return back()->with('success', 'Viewing recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Viewing $record */
        return [
            'gate' => app(GateEvaluator::class)
                ->forTransition($record, 'status', 'scheduled', $request->user())
                ->toArray(),
            'can' => $this->recordAbilities($request, $record) + [
                'schedule' => $request->user()->can('schedule', $record),
                'override' => $request->user()->can('gates.override'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'clients' => Client::orderBy('full_name')->limit(500)->get(['id', 'full_name'])
                ->map(fn (Client $client): array => ['value' => $client->id, 'label' => (string) $client->full_name])
                ->all(),
            'listings' => Listing::with('yacht:id,name')->limit(300)->get()
                ->map(fn (Listing $listing): array => [
                    'value' => $listing->id,
                    'label' => sprintf('%s · %s', $listing->reference, $listing->yacht?->name ?? 'Yacht'),
                ])
                ->all(),
            'marinas' => Marina::orderBy('name')->get(['id', 'name'])
                ->map(fn (Marina $marina): array => ['value' => $marina->id, 'label' => (string) $marina->name])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:600'],
            'attendees' => ['nullable', 'string', 'max:190'],
            'status' => ['required', Rule::in(['requested', 'scheduled', 'completed', 'cancelled', 'no_show'])],
        ]);
    }
}
