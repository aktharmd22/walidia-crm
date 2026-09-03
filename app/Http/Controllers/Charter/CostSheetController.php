<?php

declare(strict_types=1);

namespace App\Http\Controllers\Charter;

use App\Domain\Charter\CostSheetCalculator;
use App\Domain\Finance\TaxCalculator;
use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\CostSheetResource;
use App\Models\Booking;
use App\Models\CostSheet;
use App\Support\Paginate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Cost & Offer sheet: quote, invoice and actuals in one artifact (D-011).
 *
 * Which phase a user may write is decided by the policy, not the screen: Sales
 * prices the quote, Finance owns invoiced and actual.
 *
 * @extends ResourceController<CostSheet>
 */
class CostSheetController extends ResourceController
{
    protected string $model = CostSheet::class;

    protected string $name = 'cost-sheets';

    protected string $pages = 'Charter/CostSheets';

    protected string $resource = CostSheetResource::class;

    protected ?string $routePrefix = 'charter.cost-sheets';

    protected array $indexWith = ['booking.client:id,full_name', 'booking.yacht:id,name'];

    protected array $showWith = ['booking.client', 'booking.yacht', 'lines'];

    protected array $sortable = ['reference', 'total_offer', 'total_profit', 'status', 'created_at'];

    protected array $filterable = ['status'];

    /** A booking gets its sheet on demand — one per booking, always. */
    public function forBooking(Request $request, Booking $booking, CostSheetCalculator $calculator): RedirectResponse
    {
        $this->authorize('view', $booking);

        $sheet = $booking->costSheet ?? DB::transaction(function () use ($booking, $calculator): CostSheet {
            $sheet = CostSheet::create([
                'booking_id' => $booking->getKey(),
                'currency' => $booking->currency,
                'status' => 'draft',
            ]);

            // The quote starts from what the client actually accepted.
            foreach ($booking->proposal?->items ?? [] as $item) {
                $sheet->lines()->create([
                    'phase' => 'quoted',
                    'section' => 'revenue',
                    'category' => $item->category ?? 'other_revenue',
                    'description' => $item->description_en,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => round((float) $item->quantity * (float) $item->unit_price, 2),
                    'tax_rate' => $item->tax_rate,
                    'tax_treatment' => $item->tax_treatment,
                    'tax_amount' => $item->tax_amount,
                ]);
            }

            $calculator->recalculate($sheet->refresh());

            return $sheet;
        });

        return redirect()->route('charter.cost-sheets.show', $sheet);
    }

    public function storeLine(Request $request, CostSheet $sheet, TaxCalculator $tax, CostSheetCalculator $calculator): RedirectResponse
    {
        $data = $this->validatedLine($request);

        $this->authorize('writePhase', [$sheet, $data['phase']]);

        $line = $tax->line((float) $data['quantity'], (float) $data['unit_price'], $data['category']);

        $sheet->lines()->create($data + [
            'amount' => $line['amount'],
            'tax_rate' => $line['tax_rate'],
            'tax_treatment' => $line['tax_treatment'],
            'tax_amount' => $line['tax_amount'],
        ]);

        $calculator->recalculate($sheet->refresh());

        return back()->with('success', 'Line added.');
    }

    public function updateLine(Request $request, CostSheet $sheet, int $lineId, TaxCalculator $tax, CostSheetCalculator $calculator): RedirectResponse
    {
        $data = $this->validatedLine($request);

        $this->authorize('writePhase', [$sheet, $data['phase']]);

        $line = $tax->line((float) $data['quantity'], (float) $data['unit_price'], $data['category']);

        $sheet->lines()->whereKey($lineId)->update($data + [
            'amount' => $line['amount'],
            'tax_rate' => $line['tax_rate'],
            'tax_treatment' => $line['tax_treatment'],
            'tax_amount' => $line['tax_amount'],
        ]);

        $calculator->recalculate($sheet->refresh());

        return back()->with('success', 'Line updated.');
    }

    public function destroyLine(Request $request, CostSheet $sheet, int $lineId, CostSheetCalculator $calculator): RedirectResponse
    {
        $line = $sheet->lines()->findOrFail($lineId);

        $this->authorize('writePhase', [$sheet, $line->phase]);

        $line->delete();
        $calculator->recalculate($sheet->refresh());

        return back()->with('success', 'Line removed.');
    }

    /** Quote → invoice → actual, without anyone retyping twenty lines. */
    public function copyPhase(Request $request, CostSheet $sheet, CostSheetCalculator $calculator): RedirectResponse
    {
        $data = $request->validate([
            'from' => ['required', Rule::in(['quoted', 'invoiced', 'actual'])],
            'to' => ['required', Rule::in(['invoiced', 'actual']), 'different:from'],
        ]);

        $this->authorize('writePhase', [$sheet, $data['to']]);

        $copied = $calculator->copyPhase($sheet, $data['from'], $data['to']);

        return back()->with('success', "{$copied} lines copied into the {$data['to']} phase.");
    }

    /**
     * Closing is a hard gate: every payout issued, every receipt generated.
     */
    public function close(Request $request, CostSheet $sheet, GateEvaluator $gates): RedirectResponse
    {
        $this->authorize('close', $sheet);

        $reason = $request->filled('override_reason') && $request->user()->can('gates.override')
            ? (string) $request->input('override_reason')
            : null;

        $reason !== null
            ? $gates->override($sheet->booking, 'cost-sheets.close', $request->user(), $reason)
            : $gates->assertAction($sheet->booking, 'cost-sheets.close', $request->user());

        $sheet->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $request->user()->id,
        ])->save();

        $sheet->booking->logActivity('status_change', 'Cost sheet closed', 'Charter P&L is final.');

        return back()->with('success', 'Cost sheet closed. The P&L for this charter is final.');
    }

    /** The P&L: quoted against actual, per category. */
    public function profitAndLoss(Request $request, CostSheetCalculator $calculator): Response
    {
        $this->authorize('viewAny', CostSheet::class);

        $sheets = CostSheet::query()
            ->with(['booking.client:id,full_name', 'booking.yacht:id,name', 'lines'])
            ->latest()
            ->paginate(25);

        return Inertia::render('Charter/ProfitAndLoss', [
            'rows' => Paginate::shape($sheets->through(fn (CostSheet $sheet): array => [
                'id' => $sheet->id,
                'reference' => $sheet->reference,
                'booking' => $sheet->booking?->reference,
                'client' => $sheet->booking?->client?->full_name,
                'yacht' => $sheet->booking?->yacht?->name,
                'phase' => $sheet->effectivePhase(),
                'offer' => $sheet->total_offer,
                'cost' => $sheet->total_cost,
                'profit' => $sheet->total_profit,
                'margin' => $sheet->margin_pct,
                'currency' => $sheet->currency,
                'url' => route('charter.cost-sheets.show', $sheet->id),
            ])),
            'totals' => [
                'offer' => (float) CostSheet::sum('total_offer'),
                'cost' => (float) CostSheet::sum('total_cost'),
                'profit' => (float) CostSheet::sum('total_profit'),
            ],
        ]);
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var CostSheet $record */
        return [
            'variance' => app(CostSheetCalculator::class)->variance($record),
            'categories' => [
                'revenue' => CostSheet::REVENUE_CATEGORIES,
                'cost' => CostSheet::COST_CATEGORIES,
            ],
            'can' => [
                'close' => $request->user()->can('close', $record),
                'override' => $request->user()->can('gates.override'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedLine(Request $request): array
    {
        return $request->validate([
            'phase' => ['required', Rule::in(['quoted', 'invoiced', 'actual'])],
            'section' => ['required', Rule::in(['revenue', 'cost'])],
            'category' => ['required', 'string', 'max:48'],
            'description' => ['nullable', 'string', 'max:190'],
            'quantity' => ['required', 'numeric'],
            'unit_price' => ['required', 'numeric'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
