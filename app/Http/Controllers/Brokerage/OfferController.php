<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\OfferResource;
use App\Models\Client;
use App\Models\Listing;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * An offer on a listing.
 *
 * Submission is guarded on proof of funds, because a seller taking the yacht
 * off the market is entitled to know the buyer can complete.
 *
 * @extends ResourceController<Offer>
 */
class OfferController extends ResourceController
{
    protected string $model = Offer::class;

    protected string $name = 'offers';

    protected string $pages = 'Brokerage/Offers';

    protected string $resource = OfferResource::class;

    protected ?string $routePrefix = 'brokerage.offers';

    protected array $indexWith = ['client:id,full_name', 'listing:id,reference'];

    protected array $showWith = ['client', 'listing.yacht', 'surveys'];

    protected array $sortable = ['reference', 'amount', 'status', 'submitted_at'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'listing_id', 'client_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Offer::class);

        $record = Offer::create($this->validated($request));

        return redirect()->route('brokerage.offers.show', $record)->with('success', 'Offer created.');
    }

    public function update(Request $request, Offer $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Offer updated.');
    }

    public function submit(Request $request, Offer $offer, GateEvaluator $gates): RedirectResponse
    {
        $this->authorize('submit', $offer);

        $reason = $request->filled('override_reason') && $request->user()->can('gates.override')
            ? (string) $request->input('override_reason')
            : null;

        $reason !== null
            ? $gates->override($offer, 'offers.submit', $request->user(), $reason)
            : $gates->assertTransition($offer, 'status', 'submitted', $request->user());

        $offer->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();
        $offer->listing?->forceFill(['status' => 'under_offer'])->save();
        $offer->logActivity('status_change', 'Offer submitted to the seller');

        return back()->with('success', 'Offer submitted.');
    }

    /** Accepted, countered or rejected — always with the seller's words attached. */
    public function respond(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorize('respond', $offer);

        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'countered', 'rejected'])],
            'response_notes' => ['required', 'string', 'max:2000'],
        ]);

        $offer->forceFill($data + ['responded_at' => now()])->save();
        $offer->logActivity('status_change', "Offer {$data['status']}", $data['response_notes']);

        return back()->with('success', 'Response recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Offer $record */
        return [
            'gate' => app(GateEvaluator::class)
                ->forTransition($record, 'status', 'submitted', $request->user())
                ->toArray(),
            'can' => $this->recordAbilities($request, $record) + [
                'submit' => $request->user()->can('submit', $record),
                'respond' => $request->user()->can('respond', $record),
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
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'subject_to_survey' => ['boolean'],
            'subject_to_sea_trial' => ['boolean'],
            'proof_of_funds_received' => ['boolean'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'conditions' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'countered', 'accepted', 'rejected', 'withdrawn', 'lapsed'])],
        ]);
    }
}
