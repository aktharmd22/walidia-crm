<?php

declare(strict_types=1);

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\PayoutResource;
use App\Models\Client;
use App\Models\Payout;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Money leaving the company: sellers, co-brokers, referrers,
 * vendors and crew.
 *
 * Approving a payout and paying it are two acts by two people. That is not
 * ceremony — it is the control that stops a single mistake becoming a wire.
 *
 * @extends ResourceController<Payout>
 */
class PayoutController extends ResourceController
{
    protected string $model = Payout::class;

    protected string $name = 'payouts';

    protected string $pages = 'Finance/Payouts';

    protected string $resource = PayoutResource::class;

    protected ?string $routePrefix = 'finance.payouts';

    protected array $indexWith = ['payeeVendor:id,legal_name,trade_name'];

    protected array $showWith = ['transaction', 'booking', 'deal', 'payeeClient', 'payeeVendor'];

    protected array $sortable = ['reference', 'amount', 'due_on', 'status'];

    protected string $defaultSort = 'due_on';

    protected array $filterable = ['type', 'status', 'transaction_id', 'booking_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Payout::class);

        $record = Payout::create($this->validated($request));

        return redirect()->route('finance.payouts.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, Payout $payout): RedirectResponse
    {
        $this->authorize('update', $payout);

        $payout->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    public function approve(Request $request, Payout $payout): RedirectResponse
    {
        $this->authorize('approve', $payout);

        $payout->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        $payout->logActivity('status_change', 'Payout approved');

        return back()->with('success', 'Payout approved.');
    }

    /** Recording the wire, with the bank's own reference against it. */
    public function pay(Request $request, Payout $payout): RedirectResponse
    {
        $this->authorize('pay', $payout);

        $data = $request->validate([
            'bank_reference' => ['required', 'string', 'max:90'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $payout->forceFill([
            'bank_reference' => $data['bank_reference'],
            'status' => 'paid',
            'paid_at' => $data['paid_at'] ?? now(),
            'paid_by' => $request->user()->id,
        ])->save();

        $payout->logActivity('system', 'Payout paid', $data['bank_reference']);

        return back()->with('success', 'Payout recorded as paid.');
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
            'vendors' => Vendor::orderBy('legal_name')->get()
                ->map(fn (Vendor $vendor): array => ['value' => $vendor->id, 'label' => $vendor->displayName()])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        return [
            'can' => $this->recordAbilities($request, $record) + [
                'approve' => $request->user()->can('approve', $record),
                'pay' => $request->user()->can('pay', $record),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'deal_id' => ['nullable', 'integer', 'exists:deals,id'],
            'type' => ['required', Rule::in(['seller', 'co_broker', 'referral', 'vendor', 'crew'])],
            'payee_name' => ['required', 'string', 'max:190'],
            'payee_client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'payee_vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'method' => ['required', Rule::in(['bank_transfer', 'cheque', 'cash'])],
            'due_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['pending', 'approved', 'paid', 'cancelled'])],
        ]);
    }
}
