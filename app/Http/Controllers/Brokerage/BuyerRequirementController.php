<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Domain\Brokerage\ListingMatcher;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\BuyerRequirementResource;
use App\Models\BuyerRequirement;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What a buyer is looking for, written down once so every broker matches against the same brief.
 *
 * @extends ResourceController<BuyerRequirement>
 */
class BuyerRequirementController extends ResourceController
{
    protected string $model = BuyerRequirement::class;

    protected string $name = 'buyer-requirements';

    protected string $pages = 'Brokerage/BuyerRequirements';

    protected string $resource = BuyerRequirementResource::class;

    protected ?string $routePrefix = 'brokerage.buyer-requirements';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'assignee'];

    protected array $sortable = ['reference', 'budget_max', 'status', 'created_at'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'assigned_user_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BuyerRequirement::class);

        $record = BuyerRequirement::create($this->validated($request));

        return redirect()->route('brokerage.buyer-requirements.show', $record)->with('success', 'BuyerRequirement created.');
    }

    public function update(Request $request, BuyerRequirement $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'BuyerRequirement updated.');
    }

    /**
     * Listings that fit the brief. Not a score — a filter, with the reasons
     * visible, because a broker will not trust a number they cannot check.
     *
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var BuyerRequirement $record */
        $matches = app(ListingMatcher::class)->match($record);

        return [
            'matches' => $matches->map(fn (array $row): array => [
                'id' => $row['listing']->id,
                'reference' => $row['listing']->reference,
                'yacht' => $row['listing']->yacht?->name,
                'builder' => $row['listing']->yacht?->builder,
                'year_built' => $row['listing']->yacht?->year_built,
                'loa_m' => $row['listing']->yacht?->loa_m,
                'asking_price' => $row['listing']->asking_price,
                'currency' => $row['listing']->currency,
                'status' => $row['listing']->status,
                'score' => $row['score'],
                // The reasons travel with the score, so a broker can defend
                // the shortlist rather than recite a number.
                'reasons' => $row['reasons'],
                'url' => route('brokerage.listings.show', $row['listing']->id),
            ])->all(),
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'loa_min' => ['nullable', 'integer', 'min:0', 'max:200'],
            'loa_max' => ['nullable', 'integer', 'min:0', 'max:200'],
            'year_from' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'use_case' => ['nullable', 'string', 'max:48'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'paused', 'fulfilled', 'lost'])],
        ]);
    }
}
