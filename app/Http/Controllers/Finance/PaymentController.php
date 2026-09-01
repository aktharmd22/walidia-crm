<?php

declare(strict_types=1);

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentScheduleItem;
use App\Models\Receipt;
use App\Services\SequenceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Money in, and what it settled.
 *
 * Clearing a payment is the single most consequential action in the finance
 * module: it is what unlocks Operational Release, and it is why `cleared_at`
 * is set deliberately rather than inferred from the payment existing.
 *
 * @extends ResourceController<Payment>
 */
class PaymentController extends ResourceController
{
    protected string $model = Payment::class;

    protected string $name = 'payments';

    protected string $pages = 'Finance/Payments';

    protected string $resource = PaymentResource::class;

    protected ?string $routePrefix = 'finance.payments';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'allocations.invoice', 'allocations.scheduleItem', 'receipt'];

    protected array $sortable = ['reference', 'received_at', 'amount', 'status'];

    protected string $defaultSort = '-received_at';

    protected array $filterable = ['status', 'method', 'client_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'method' => ['required', Rule::in(['bank_transfer', 'card', 'cash', 'cheque', 'link'])],
            'gateway_reference' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'received_at' => ['required', 'date'],
            'bank_charge_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'allocations.*.payment_schedule_item_id' => ['nullable', 'integer', 'exists:payment_schedule_items,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $payment = DB::transaction(function () use ($data): Payment {
            $payment = Payment::create([
                'client_id' => $data['client_id'] ?? null,
                'method' => $data['method'],
                'gateway_reference' => $data['gateway_reference'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'received_at' => $data['received_at'],
                'bank_charge_amount' => $data['bank_charge_amount'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            $this->allocate($payment, $data['allocations'] ?? []);

            return $payment;
        });

        return redirect()->route('finance.payments.show', $payment)
            ->with('success', 'Payment recorded. It is not cleared until Finance confirms it.');
    }

    /**
     * Confirming a deposit clears the payment, settles what it was allocated
     * to, issues the receipt, and thereby unlocks Operational Release.
     */
    public function confirmDeposit(Request $request, Payment $payment, SequenceService $sequences): RedirectResponse
    {
        $this->authorize('confirmDeposit', $payment);

        DB::transaction(function () use ($payment, $request, $sequences): void {
            $payment->forceFill([
                'status' => 'cleared',
                'cleared_at' => now(),
                'reconciled_at' => now(),
                'reconciled_by' => $request->user()->id,
            ])->save();

            foreach ($payment->allocations as $allocation) {
                $this->settle($allocation->invoice, $allocation->scheduleItem);
            }

            if ($payment->receipt === null) {
                Receipt::create([
                    'reference' => $sequences->next('receipt'),
                    'payment_id' => $payment->getKey(),
                    'client_id' => $payment->client_id,
                    'issued_at' => now(),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ]);
            }
        });

        return back()->with('success', 'Payment cleared. Operational Release can now be granted.');
    }

    public function reconcile(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('reconcile', $payment);

        $payment->forceFill([
            'reconciled_at' => now(),
            'reconciled_by' => $request->user()->id,
        ])->save();

        return back()->with('success', 'Payment reconciled.');
    }

    public function allocateTo(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $data = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'allocations.*.payment_schedule_item_id' => ['nullable', 'integer', 'exists:payment_schedule_items,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $total = array_sum(array_column($data['allocations'], 'amount'));

        abort_if(
            $total > (float) $payment->amount + 0.001,
            422,
            'Allocations cannot exceed the payment amount.',
        );

        DB::transaction(function () use ($payment, $data): void {
            $payment->allocations()->delete();
            $this->allocate($payment, $data['allocations']);
        });

        return back()->with('success', 'Payment allocated.');
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Payment $record */
        return [
            'can' => array_merge($this->recordAbilities($request, $record), [
                'confirm' => $request->user()->can('confirmDeposit', $record),
                'reconcile' => $request->user()->can('reconcile', $record),
            ]),
            'openInvoices' => Invoice::query()
                ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
                ->when($record->client_id, fn ($query) => $query->where('client_id', $record->client_id))
                ->limit(50)
                ->get(['id', 'reference', 'total', 'amount_due', 'currency']),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $allocations
     */
    private function allocate(Payment $payment, array $allocations): void
    {
        foreach ($allocations as $allocation) {
            $payment->allocations()->create([
                'invoice_id' => $allocation['invoice_id'] ?? null,
                'payment_schedule_item_id' => $allocation['payment_schedule_item_id'] ?? null,
                'amount' => $allocation['amount'],
            ]);
        }
    }

    /** Recomputes what an invoice and its instalment are owed, from cleared money. */
    private function settle(?Invoice $invoice, ?PaymentScheduleItem $item): void
    {
        if ($invoice !== null) {
            $paid = $invoice->clearedAmount();
            $due = round((float) $invoice->total - $paid, 2);

            $invoice->forceFill([
                'amount_paid' => $paid,
                'amount_due' => max($due, 0),
                'status' => $due <= 0.001 ? 'paid' : ($paid > 0 ? 'partially_paid' : $invoice->status),
            ])->save();
        }

        if ($item !== null && $item->isSettled()) {
            $item->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
        }
    }
}
