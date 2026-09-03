<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\ValuationResource;
use App\Models\Listing;
use App\Models\Valuation;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What a yacht is worth, and the working behind it.
 *
 * The comparables are kept because an asking price a broker cannot defend is
 * one that collapses at the first offer.
 *
 * @extends ResourceController<Valuation>
 */
class ValuationController extends ResourceController
{
    protected string $model = Valuation::class;

    protected string $name = 'valuations';

    protected string $pages = 'Brokerage/Valuations';

    protected string $resource = ValuationResource::class;

    protected ?string $routePrefix = 'brokerage.valuations';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht', 'listing', 'valuer'];

    protected array $sortable = ['reference', 'valued_on', 'broker_valuation'];

    protected string $defaultSort = '-valued_on';

    protected array $filterable = ['pricing_decision', 'status', 'yacht_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Valuation::class);

        $record = Valuation::create($this->validated($request));

        return redirect()->route('brokerage.valuations.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, Valuation $valuation): RedirectResponse
    {
        $this->authorize('update', $valuation);

        $valuation->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /**
     * The seller either takes the number or changes it. Recording which — and
     * why — is what makes a later price cut a decision rather than a drift.
     */
    public function decide(Request $request, Valuation $valuation): RedirectResponse
    {
        $this->authorize('decide', $valuation);

        $data = $request->validate([
            'pricing_decision' => ['required', Rule::in(['approved', 'adjusted'])],
            'agreed_asking' => ['required', 'numeric', 'min:0'],
            'adjustment_reason' => ['required_if:pricing_decision,adjusted', 'nullable', 'string', 'max:2000'],
        ]);

        $valuation->forceFill($data + ['status' => 'accepted'])->save();

        $valuation->logActivity(
            'status_change',
            "Pricing {$data['pricing_decision']}",
            $data['adjustment_reason'] ?? null,
        );

        // The listing follows the decision, so the two can never disagree.
        $valuation->listing?->forceFill(['asking_price' => $data['agreed_asking']])->save();

        return back()->with('success', 'Pricing decision recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'yachts' => Yacht::orderBy('name')->get(['id', 'name'])
                ->map(fn (Yacht $yacht): array => ['value' => $yacht->id, 'label' => (string) $yacht->name])
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
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'valued_on' => ['required', 'date'],
            'market_low' => ['nullable', 'numeric', 'min:0'],
            'market_high' => ['nullable', 'numeric', 'min:0', 'gte:market_low'],
            'broker_valuation' => ['required', 'numeric', 'min:0'],
            'recommended_asking' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'rationale' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'issued', 'accepted'])],
        ]);
    }
}
