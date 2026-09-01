<?php

declare(strict_types=1);

namespace App\Http\Controllers\Finance;

use App\Domain\Finance\TaxCalculator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\SequenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tax invoices.
 *
 * A draft is fully editable. An issued invoice is not: it takes its gapless
 * number at the moment of issue, and after that it can only be voided and
 * credited (D-013).
 *
 * @extends ResourceController<Invoice>
 */
class InvoiceController extends ResourceController
{
    protected string $model = Invoice::class;

    protected string $name = 'invoices';

    protected string $pages = 'Finance/Invoices';

    protected string $resource = InvoiceResource::class;

    protected ?string $routePrefix = 'finance.invoices';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'company', 'items', 'allocations.payment', 'subject'];

    protected array $sortable = ['reference', 'issue_date', 'due_date', 'total', 'status'];

    protected string $defaultSort = '-issue_date';

    protected array $filterable = ['status', 'type', 'client_id'];

    public function store(Request $request, TaxCalculator $tax): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $this->validated($request);

        $invoice = DB::transaction(function () use ($data, $tax): Invoice {
            $invoice = Invoice::create([
                'type' => $data['type'],
                'client_id' => $data['client_id'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'subject_type' => $data['subject_type'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'place_of_supply' => $data['place_of_supply'] ?? 'United Arab Emirates',
                'tax_treatment' => $data['tax_treatment'],
                'currency' => $data['currency'],
                'supplier_trn' => Setting::get('tax', 'trn'),
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
            ]);

            $this->writeItems($invoice, $data['items'], $tax);

            return $invoice;
        });

        return redirect()->route('finance.invoices.show', $invoice)->with('success', 'Draft invoice created.');
    }

    public function update(Request $request, Invoice $invoice, TaxCalculator $tax): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $data = $this->validated($request);

        DB::transaction(function () use ($invoice, $data, $tax): void {
            $invoice->update([
                'client_id' => $data['client_id'] ?? $invoice->client_id,
                'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'place_of_supply' => $data['place_of_supply'] ?? $invoice->place_of_supply,
                'tax_treatment' => $data['tax_treatment'],
                'currency' => $data['currency'],
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();
            $this->writeItems($invoice, $data['items'], $tax);
        });

        return back()->with('success', 'Invoice updated.');
    }

    /**
     * Issuing is the point of no return: the number is allocated from the
     * locking sequence inside the same transaction, so it is gapless even if
     * two people click at once.
     */
    public function issue(Request $request, Invoice $invoice, SequenceService $sequences): RedirectResponse
    {
        $this->authorize('issue', $invoice);

        abort_if($invoice->items()->count() === 0, 422, 'An invoice with no lines cannot be issued.');

        DB::transaction(function () use ($invoice, $sequences): void {
            $invoice->forceFill([
                'reference' => $invoice->reference ?? $sequences->next($invoice->sequenceKey()),
                'status' => 'issued',
                'issued_at' => now(),
                'issue_date' => $invoice->issue_date ?? now()->toDateString(),
                'amount_due' => $invoice->total,
            ])->save();
        });

        return back()->with('success', "Invoice {$invoice->fresh()->reference} issued.");
    }

    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);

        $data = $request->validate(['reason' => ['required', 'string', 'max:190']]);

        $invoice->forceFill([
            'status' => 'void',
            'voided_at' => now(),
            'void_reason' => $data['reason'],
        ])->save();

        return back()->with('success', 'Invoice voided. Issue a credit note if money has already moved.');
    }

    /**
     * A credit note is an invoice of its own type, so numbering, VAT and
     * reporting all stay in one place.
     */
    public function creditNote(Request $request, Invoice $invoice, SequenceService $sequences): RedirectResponse
    {
        $this->authorize('creditNote', $invoice);

        $credit = DB::transaction(function () use ($invoice, $sequences): Invoice {
            $credit = Invoice::create([
                'type' => 'credit_note',
                'credit_note_of_id' => $invoice->getKey(),
                'client_id' => $invoice->client_id,
                'company_id' => $invoice->company_id,
                'subject_type' => $invoice->subject_type,
                'subject_id' => $invoice->subject_id,
                'issue_date' => now()->toDateString(),
                'place_of_supply' => $invoice->place_of_supply,
                'tax_treatment' => $invoice->tax_treatment,
                'currency' => $invoice->currency,
                'supplier_trn' => $invoice->supplier_trn,
                'subtotal' => -1 * (float) $invoice->subtotal,
                'tax_amount' => -1 * (float) $invoice->tax_amount,
                'total' => -1 * (float) $invoice->total,
                'amount_due' => -1 * (float) $invoice->total,
                'status' => 'issued',
                'issued_at' => now(),
                'reference' => $sequences->next('credit_note'),
            ]);

            foreach ($invoice->items as $item) {
                $credit->items()->create([
                    'description_en' => $item->description_en,
                    'quantity' => $item->quantity,
                    'unit_price' => -1 * (float) $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'tax_treatment' => $item->tax_treatment,
                    'tax_amount' => -1 * (float) $item->tax_amount,
                    'line_total' => -1 * (float) $item->line_total,
                    'sort_order' => $item->sort_order,
                ]);
            }

            $invoice->forceFill(['status' => 'credited'])->save();

            return $credit;
        });

        return redirect()->route('finance.invoices.show', $credit)
            ->with('success', "Credit note {$credit->reference} issued against {$invoice->reference}.");
    }

    public function overdue(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        return Inertia::render('Finance/Invoices/Index', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->overdue()),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => 'Overdue invoices',
        ]);
    }

    protected function indexProps(Request $request): array
    {
        return ['heading' => 'Invoices'];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Invoice $record */
        return [
            'can' => array_merge($this->recordAbilities($request, $record), [
                'issue' => $request->user()->can('issue', $record),
                'void' => $request->user()->can('void', $record),
                'credit' => $request->user()->can('creditNote', $record),
            ]),
            'cleared' => $record->clearedAmount(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function writeItems(Invoice $invoice, array $items, TaxCalculator $tax): void
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($items as $index => $item) {
            $line = $tax->line(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (string) ($item['category'] ?? 'other'),
                treatmentOverride: $invoice->tax_treatment === 'standard' ? null : $invoice->tax_treatment,
            );

            $invoice->items()->create([
                'description_en' => $item['description_en'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'unit_price' => $item['unit_price'],
                'tax_rate' => $line['tax_rate'],
                'tax_treatment' => $line['tax_treatment'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
                'sort_order' => $index,
            ]);

            $subtotal += $line['amount'];
            $taxTotal += $line['tax_amount'];
        }

        $total = round($subtotal + $taxTotal, 2);

        $invoice->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxTotal, 2),
            'total' => $total,
            'amount_due' => round($total - (float) $invoice->amount_paid, 2),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['tax_invoice', 'proforma'])],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'subject_type' => ['nullable', 'string', 'max:64'],
            'subject_id' => ['nullable', 'integer'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'place_of_supply' => ['nullable', 'string', 'max:90'],
            'tax_treatment' => ['required', Rule::in(config('walidia.tax.treatments'))],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description_en' => ['required', 'string', 'max:190'],
            'items.*.category' => ['nullable', 'string', 'max:48'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:24'],
            'items.*.unit_price' => ['required', 'numeric'],
        ]);
    }
}
