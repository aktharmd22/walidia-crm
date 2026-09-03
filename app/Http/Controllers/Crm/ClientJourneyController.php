<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\ClientJourneyResource;
use App\Models\Client;
use App\Models\ClientJourney;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What happens after the money is settled.
 *
 * Both flowcharts end here, and it is the part most systems leave to a
 * spreadsheet — which is why repeat business is where they leak.
 *
 * @extends ResourceController<ClientJourney>
 */
class ClientJourneyController extends ResourceController
{
    protected string $model = ClientJourney::class;

    protected string $name = 'client-journeys';

    protected string $pages = 'Crm/Journeys';

    protected string $resource = ClientJourneyResource::class;

    protected ?string $routePrefix = 'crm.journeys';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'booking', 'transaction'];

    protected array $sortable = ['created_at', 'satisfaction_score', 'status'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['type', 'status', 'complaint_raised'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ClientJourney::class);

        $record = ClientJourney::create($this->validated($request));

        return redirect()->route('crm.journeys.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, ClientJourney $clientJourney): RedirectResponse
    {
        $this->authorize('update', $clientJourney);

        $clientJourney->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /** The client said something. Record it, score it, and act on it. */
    public function recordSurvey(Request $request, ClientJourney $clientJourney): RedirectResponse
    {
        $this->authorize('update', $clientJourney);

        $data = $request->validate([
            'satisfaction_score' => ['required', 'integer', 'min:1', 'max:5'],
            'survey_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $clientJourney->forceFill($data)->save();
        $clientJourney->logActivity('note', "Satisfaction {$data['satisfaction_score']} / 5", $data['survey_response'] ?? null);

        return back()->with('success', 'Response recorded.');
    }

    /**
     * A complaint is raised and resolved as two acts, because the gap between
     * them is the thing worth measuring.
     */
    public function raiseComplaint(Request $request, ClientJourney $clientJourney): RedirectResponse
    {
        $this->authorize('update', $clientJourney);

        $data = $request->validate(['complaint_detail' => ['required', 'string', 'max:5000']]);

        $clientJourney->forceFill($data + ['complaint_raised' => true])->save();
        $clientJourney->logActivity('note', 'Complaint raised', $data['complaint_detail']);

        return back()->with('warning', 'Complaint recorded.');
    }

    public function resolveComplaint(Request $request, ClientJourney $clientJourney): RedirectResponse
    {
        $this->authorize('update', $clientJourney);

        $data = $request->validate(['complaint_resolution' => ['required', 'string', 'min:20', 'max:5000']]);

        $clientJourney->forceFill($data + ['complaint_resolved_at' => now()])->save();
        $clientJourney->logActivity('note', 'Complaint resolved', $data['complaint_resolution']);

        return back()->with('success', 'Complaint resolved.');
    }

    /** What the client might want next, in their own words. */
    public function recordUpsell(Request $request, ClientJourney $clientJourney): RedirectResponse
    {
        $this->authorize('update', $clientJourney);

        $data = $request->validate([
            'upsell_interests' => ['required', 'array'],
            'upsell_interests.*' => [Rule::in([
                'brokerage', 'yacht_sales', 'management', 'crew_services',
                'maintenance', 'annual_package', 'membership', 'insurance',
            ])],
        ]);

        $clientJourney->forceFill($data)->save();

        return back()->with('success', 'Interests recorded.');
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'type' => ['required', Rule::in(['post_charter', 'post_sale'])],
            'satisfaction_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'survey_response' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['open', 'complete', 'lapsed'])],
        ]);
    }
}
