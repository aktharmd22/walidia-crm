<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\TransactionResource;
use App\Models\Client;
use App\Models\Listing;
use App\Models\Transaction;
use App\Models\YachtOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The sale: contract, escrow, AML, transfer.
 *
 * Ownership does not move until the money has cleared and AML is clear. That
 * gate protects the brokerage's licence, so it is hard and it is logged.
 *
 * @extends ResourceController<Transaction>
 */
class TransactionController extends ResourceController
{
    protected string $model = Transaction::class;

    protected string $name = 'transactions';

    protected string $pages = 'Brokerage/Transactions';

    protected string $resource = TransactionResource::class;

    protected ?string $routePrefix = 'brokerage.transactions';

    protected array $indexWith = ['listing:id,reference', 'buyer:id,full_name'];

    protected array $showWith = ['listing.yacht', 'buyer', 'seller', 'offer', 'handover'];

    protected array $sortable = ['reference', 'agreed_price', 'status', 'expected_closing_on'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'listing_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $record = Transaction::create($this->validated($request));

        return redirect()->route('brokerage.transactions.show', $record)->with('success', 'Transaction created.');
    }

    public function update(Request $request, Transaction $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Transaction updated.');
    }

    /** Recording that money has arrived — the fact the transfer gate reads. */
    public function recordFunds(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $data = $request->validate([
            'leg' => ['required', Rule::in(['deposit', 'balance'])],
            'cleared_at' => ['required', 'date', 'before_or_equal:now'],
        ]);

        $transaction->forceFill([
            "{$data['leg']}_cleared_at" => $data['cleared_at'],
            'status' => $data['leg'] === 'balance' ? 'funds_pending' : $transaction->status,
        ])->save();

        $transaction->logActivity('system', ucfirst($data['leg']).' cleared');

        return back()->with('success', ucfirst($data['leg']).' recorded as cleared.');
    }

    public function clearAml(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('clearAml', $transaction);

        $data = $request->validate(['notes' => ['required', 'string', 'min:20', 'max:2000']]);

        $transaction->forceFill([
            'aml_cleared' => true,
            'aml_cleared_at' => now(),
            'aml_cleared_by' => $request->user()->id,
        ])->save();

        $transaction->logActivity('system', 'AML screening cleared', $data['notes']);

        return back()->with('success', 'AML screening recorded as clear.');
    }

    /**
     * The hard one. Everything above exists so that this cannot happen early.
     */
    public function transferOwnership(Request $request, Transaction $transaction, GateEvaluator $gates): RedirectResponse
    {
        $this->authorize('transferOwnership', $transaction);

        $reason = $request->filled('override_reason') && $request->user()->can('gates.override')
            ? (string) $request->input('override_reason')
            : null;

        $reason !== null
            ? $gates->override($transaction, 'transactions.transfer-ownership', $request->user(), $reason)
            : $gates->assertAction($transaction, 'transactions.transfer-ownership', $request->user());

        $transaction->forceFill([
            'ownership_transferred_at' => now(),
            'ownership_transferred_by' => $request->user()->id,
            'status' => 'completed',
        ])->save();

        $transaction->listing?->forceFill(['status' => 'sold', 'is_published' => false])->save();
        $transaction->logActivity('status_change', 'Ownership transferred');

        return back()->with('success', 'Ownership transferred. The listing is marked sold.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Transaction $record */
        return [
            'gate' => app(GateEvaluator::class)
                ->forAction($record, 'transactions.transfer-ownership', $request->user())
                ->toArray(),
            'can' => $this->recordAbilities($request, $record) + [
                'transfer' => $request->user()->can('transferOwnership', $record),
                'clearAml' => $request->user()->can('clearAml', $record),
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
            'listings' => Listing::with('yacht:id,name')->limit(300)->get()
                ->map(fn (Listing $listing): array => [
                    'value' => $listing->id,
                    'label' => sprintf('%s · %s', $listing->reference, $listing->yacht?->name ?? 'Yacht'),
                ])
                ->all(),
            'clients' => Client::orderBy('full_name')->limit(500)->get(['id', 'full_name'])
                ->map(fn (Client $client): array => ['value' => $client->id, 'label' => (string) $client->full_name])
                ->all(),
            'owners' => YachtOwner::with('client:id,full_name')->get()
                ->map(fn (YachtOwner $owner): array => [
                    'value' => $owner->id,
                    'label' => (string) ($owner->client?->full_name ?? 'Owner'),
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
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'buyer_client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'seller_owner_id' => ['nullable', 'integer', 'exists:yacht_owners,id'],
            'agreed_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'balance_amount' => ['nullable', 'numeric', 'min:0'],
            'escrow_agent' => ['nullable', 'string', 'max:190'],
            'contract_type' => ['required', Rule::in(['myba', 'moa', 'bespoke'])],
            'contract_signed_on' => ['nullable', 'date'],
            'expected_closing_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'under_contract', 'funds_pending', 'transferring', 'completed', 'aborted'])],
        ]);
    }
}
