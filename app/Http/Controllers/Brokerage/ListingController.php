<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Models\User;
use App\Models\Yacht;
use App\Models\YachtOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Yachts for sale.
 *
 * The mandate's expiry is the soft gate here: a lapsed central agency agreement
 * is a commission the brokerage has earned and cannot collect.
 *
 * @extends ResourceController<Listing>
 */
class ListingController extends ResourceController
{
    protected string $model = Listing::class;

    protected string $name = 'listings';

    protected string $pages = 'Brokerage/Listings';

    protected string $resource = ListingResource::class;

    protected ?string $routePrefix = 'brokerage.listings';

    protected array $indexWith = ['yacht:id,name', 'assignee:id,name'];

    protected array $showWith = ['yacht', 'owner', 'assignee', 'ndas.client', 'viewings.client', 'offers.client', 'surveys'];

    protected array $sortable = ['reference', 'asking_price', 'status', 'agreement_expires_on', 'created_at'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'mandate_type', 'assigned_user_id', 'is_published'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Listing::class);

        $record = Listing::create($this->validated($request));

        return redirect()->route('brokerage.listings.show', $record)->with('success', 'Listing created.');
    }

    public function update(Request $request, Listing $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Listing updated.');
    }

    /** Publishing puts the yacht in front of buyers — and starts the clock. */
    public function publish(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('publish', $listing);

        $listing->forceFill([
            'is_published' => true,
            'listed_on' => $listing->listed_on ?? now()->toDateString(),
            'status' => $listing->status === 'draft' ? 'active' : $listing->status,
        ])->save();

        $listing->logActivity('status_change', 'Listing published');

        return back()->with('success', 'Listing published.');
    }

    public function withdraw(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $listing->forceFill(['status' => 'withdrawn', 'is_published' => false])->save();
        $listing->logActivity('status_change', 'Listing withdrawn', $data['reason']);

        return back()->with('success', 'Listing withdrawn.');
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
            'owners' => YachtOwner::with('client:id,full_name')->get()
                ->map(fn (YachtOwner $owner): array => [
                    'value' => $owner->id,
                    'label' => (string) ($owner->client?->full_name ?? 'Owner'),
                ])
                ->all(),
            'users' => User::orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => (string) $user->name])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Listing $record */
        return [
            'can' => $this->recordAbilities($request, $record) + [
                'publish' => $request->user()->can('publish', $record),
            ],
            // The soft gate is shown as a warning, never as a blocked button.
            'agreementGate' => app(GateEvaluator::class)
                ->forAction($record, 'daily.expiry-scan', $request->user())
                ->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'yacht_owner_id' => ['nullable', 'integer', 'exists:yacht_owners,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'mandate_type' => ['required', Rule::in(['central', 'co_central', 'open'])],
            'asking_price' => ['required', 'numeric', 'min:0'],
            'reserve_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:20'],
            'agreement_signed_on' => ['nullable', 'date'],
            'agreement_expires_on' => ['nullable', 'date', 'after:agreement_signed_on'],
            'requires_nda' => ['boolean'],
            'requires_proof_of_funds' => ['boolean'],
            'is_published' => ['boolean'],
            'marketing_summary' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'active', 'under_offer', 'sold', 'withdrawn', 'expired'])],
        ]);
    }
}
