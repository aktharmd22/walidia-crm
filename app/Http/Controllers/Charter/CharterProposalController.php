<?php

declare(strict_types=1);

namespace App\Http\Controllers\Charter;

use App\Domain\Charter\Actions\AcceptProposal;
use App\Domain\Finance\TaxCalculator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\CharterProposalResource;
use App\Models\CharterEnquiry;
use App\Models\CharterProposal;
use App\Models\CostSheet;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Proposals: priced, versioned, sent, accepted.
 *
 * Totals are computed server-side from the lines. Nothing about money is taken
 * on the browser's word.
 *
 * @extends ResourceController<CharterProposal>
 */
class CharterProposalController extends ResourceController
{
    protected string $model = CharterProposal::class;

    protected string $name = 'charter-proposals';

    protected string $pages = 'Charter/Proposals';

    protected string $resource = CharterProposalResource::class;

    protected ?string $routePrefix = 'charter.proposals';

    protected array $indexWith = ['client:id,full_name', 'enquiry:id,reference'];

    protected array $showWith = ['client', 'enquiry.pickupMarina', 'items.yacht'];

    protected array $sortable = ['reference', 'total', 'status', 'created_at'];

    protected array $filterable = ['status'];

    public function store(Request $request, TaxCalculator $tax): RedirectResponse
    {
        $this->authorize('create', CharterProposal::class);

        $data = $request->validate([
            'charter_enquiry_id' => ['required', 'integer', 'exists:charter_enquiries,id'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'items.*.type' => ['required', Rule::in(['charter', 'extra', 'discount'])],
            'items.*.category' => ['nullable', 'string', 'max:48'],
            'items.*.description_en' => ['required', 'string', 'max:190'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:24'],
            'items.*.unit_price' => ['required', 'numeric'],
        ]);

        $enquiry = CharterEnquiry::findOrFail($data['charter_enquiry_id']);

        $proposal = DB::transaction(function () use ($data, $enquiry, $tax, $request): CharterProposal {
            $version = (int) $enquiry->proposals()->max('version') + 1;

            $proposal = CharterProposal::create([
                'charter_enquiry_id' => $enquiry->getKey(),
                'client_id' => $enquiry->client_id,
                'version' => $version,
                'valid_until' => $data['valid_until'] ?? now()->addDays(7),
                'currency' => $data['currency'],
                'terms' => $data['terms'] ?? null,
                'status' => 'draft',
                'assigned_user_id' => $enquiry->assigned_user_id ?? $request->user()->id,
            ]);

            $this->writeItems($proposal, $data['items'], $tax);

            $enquiry->forceFill(['status' => 'proposed'])->save();
            $proposal->logActivity('system', "Proposal v{$version} drafted");

            return $proposal;
        });

        return redirect()->route('charter.proposals.show', $proposal)
            ->with('success', "Proposal {$proposal->reference} created.");
    }

    public function update(Request $request, CharterProposal $proposal, TaxCalculator $tax): RedirectResponse
    {
        $this->authorize('update', $proposal);

        abort_unless($proposal->status === 'draft', 422, 'A sent proposal is versioned, not edited.');

        $data = $request->validate([
            'valid_until' => ['nullable', 'date'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'items.*.type' => ['required', Rule::in(['charter', 'extra', 'discount'])],
            'items.*.category' => ['nullable', 'string', 'max:48'],
            'items.*.description_en' => ['required', 'string', 'max:190'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric'],
        ]);

        DB::transaction(function () use ($proposal, $data, $tax): void {
            $proposal->update([
                'valid_until' => $data['valid_until'] ?? $proposal->valid_until,
                'terms' => $data['terms'] ?? null,
            ]);

            $proposal->items()->delete();
            $this->writeItems($proposal, $data['items'], $tax);
        });

        return back()->with('success', 'Proposal updated.');
    }

    public function send(Request $request, CharterProposal $proposal): RedirectResponse
    {
        $this->authorize('send', $proposal);

        $proposal->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        $proposal->logActivity('email', 'Proposal sent to the client', direction: 'outbound');

        return back()->with('success', "Proposal {$proposal->reference} sent.");
    }

    /**
     * Acceptance locks the yacht, opens the booking and lays down the payment
     * schedule — the first hard gate in the charter flow.
     */
    public function accept(Request $request, CharterProposal $proposal, AcceptProposal $action): RedirectResponse
    {
        $this->authorize('accept', $proposal);

        $booking = $action->execute($proposal, $request->user());

        return redirect()->route('charter.bookings.show', $booking)
            ->with('success', "Accepted. Booking {$booking->reference} opened and the yacht is held.");
    }

    public function decline(Request $request, CharterProposal $proposal): RedirectResponse
    {
        $this->authorize('update', $proposal);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:190']]);

        $proposal->forceFill([
            'status' => 'declined',
            'responded_at' => now(),
            'decline_reason' => $data['reason'] ?? null,
        ])->save();

        $proposal->logActivity('status_change', 'Proposal declined', $data['reason'] ?? null);

        return back()->with('success', 'Proposal marked declined.');
    }

    /** A new version supersedes rather than overwrites — what they saw is kept. */
    public function version(Request $request, CharterProposal $proposal): RedirectResponse
    {
        $this->authorize('create', CharterProposal::class);

        $copy = DB::transaction(function () use ($proposal, $request): CharterProposal {
            $copy = $proposal->replicate(['status', 'sent_at', 'viewed_at', 'responded_at', 'reference']);
            $copy->forceFill([
                'version' => (int) $proposal->enquiry->proposals()->max('version') + 1,
                'supersedes_id' => $proposal->getKey(),
                'status' => 'draft',
                'reference' => null,
                'assigned_user_id' => $request->user()->id,
            ])->save();

            foreach ($proposal->items as $item) {
                $copy->items()->create($item->only([
                    'yacht_id', 'type', 'category', 'description_en', 'description_ar',
                    'quantity', 'unit', 'unit_price', 'tax_rate', 'tax_treatment',
                    'tax_amount', 'line_total', 'sort_order',
                ]));
            }

            return $copy;
        });

        return redirect()->route('charter.proposals.show', $copy)
            ->with('success', "Version {$copy->version} drafted from {$proposal->reference}.");
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'yachts' => Yacht::charterFleet()->with('charterProfile')->orderBy('name')->get()
                ->map(fn (Yacht $yacht): array => [
                    'value' => $yacht->id,
                    'label' => $yacht->name,
                    'day_rate' => $yacht->charterProfile?->full_day_rate,
                    'hourly_rate' => $yacht->charterProfile?->hourly_rate,
                ]),
            'categories' => CostSheet::REVENUE_CATEGORIES,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function writeItems(CharterProposal $proposal, array $items, TaxCalculator $tax): void
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($items as $index => $item) {
            $line = $tax->line(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (string) ($item['category'] ?? 'other'),
            );

            $proposal->items()->create([
                'yacht_id' => $item['yacht_id'] ?? null,
                'type' => $item['type'],
                'category' => $item['category'] ?? null,
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

        $proposal->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxTotal, 2),
            'total' => round($subtotal + $taxTotal, 2),
        ])->save();
    }
}
