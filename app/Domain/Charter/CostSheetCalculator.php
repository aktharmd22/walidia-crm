<?php

declare(strict_types=1);

namespace App\Domain\Charter;

use App\Models\CostSheet;
use App\Models\CostSheetLine;
use Illuminate\Support\Collection;

/**
 * The arithmetic behind the Cost & Offer table.
 *
 * Totals are recomputed from the lines on every write and stored — never
 * accepted from the browser — so the P&L cannot disagree with what it is
 * derived from. Commission is the one number the business must confirm before
 * this ships (Q4): the basis is configurable, not assumed.
 */
class CostSheetCalculator
{
    /**
     * Recompute and persist the totals for a sheet, using its effective phase:
     * actuals if they exist, otherwise invoiced, otherwise the quote.
     *
     * @return array{offer: float, cost: float, profit: float, margin: float}
     */
    public function recalculate(CostSheet $sheet): array
    {
        $sheet->loadMissing('lines');

        $phase = $sheet->effectivePhase();
        $lines = $sheet->lines->where('phase', $phase);

        $offer = $this->sum($lines->where('section', 'revenue'));
        $cost = $this->sum($lines->where('section', 'cost'));
        $profit = round($offer - $cost, 2);
        $margin = $offer > 0 ? round($profit / $offer * 100, 2) : 0.0;

        $sheet->forceFill([
            'total_offer' => $offer,
            'total_cost' => $cost,
            'total_profit' => $profit,
            'margin_pct' => $margin,
        ])->save();

        return ['offer' => $offer, 'cost' => $cost, 'profit' => $profit, 'margin' => $margin];
    }

    /**
     * Quoted versus actual, line by line — the variance view the phased model
     * gives us for nothing (D-011).
     *
     * @return list<array{category: string, section: string, quoted: float, actual: float, variance: float}>
     */
    public function variance(CostSheet $sheet): array
    {
        $sheet->loadMissing('lines');

        $quoted = $this->byCategory($sheet->lines->where('phase', 'quoted'));
        $actual = $this->byCategory($sheet->lines->where('phase', 'actual'));

        $rows = [];

        foreach (array_unique([...array_keys($quoted), ...array_keys($actual)]) as $key) {
            [$section, $category] = explode('|', $key, 2);

            $quotedAmount = $quoted[$key] ?? 0.0;
            $actualAmount = $actual[$key] ?? 0.0;

            $rows[] = [
                'category' => $category,
                'section' => $section,
                'quoted' => $quotedAmount,
                'actual' => $actualAmount,
                // Cost overruns and revenue shortfalls both read as negative.
                'variance' => round(
                    $section === 'revenue' ? $actualAmount - $quotedAmount : $quotedAmount - $actualAmount,
                    2,
                ),
            ];
        }

        usort($rows, fn (array $a, array $b): int => [$a['section'], $a['category']] <=> [$b['section'], $b['category']]);

        return $rows;
    }

    /**
     * Copies one phase forward — quote to invoice, invoice to actual — so
     * nobody retypes twenty lines and introduces a difference by hand.
     */
    public function copyPhase(CostSheet $sheet, string $from, string $to): int
    {
        $sheet->loadMissing('lines');

        $source = $sheet->lines->where('phase', $from);

        if ($source->isEmpty()) {
            return 0;
        }

        $sheet->lines()->where('phase', $to)->delete();

        foreach ($source as $line) {
            $sheet->lines()->create(array_merge(
                $line->only([
                    'section', 'category', 'description', 'quantity', 'unit_price', 'amount',
                    'tax_rate', 'tax_treatment', 'tax_amount', 'is_taxable', 'vendor_id', 'crew_id',
                    'meta', 'sort_order',
                ]),
                ['phase' => $to],
            ));
        }

        $this->recalculate($sheet->refresh());

        return $source->count();
    }

    /**
     * @param  Collection<int, CostSheetLine>  $lines
     */
    private function sum(Collection $lines): float
    {
        // Integer fils, summed, then converted once.
        $fils = $lines->reduce(
            fn (int $carry, CostSheetLine $line): int => $carry + (int) round((float) $line->amount * 100),
            0,
        );

        return $fils / 100;
    }

    /**
     * @param  Collection<int, CostSheetLine>  $lines
     * @return array<string, float>
     */
    private function byCategory(Collection $lines): array
    {
        $totals = [];

        foreach ($lines as $line) {
            $key = "{$line->section}|{$line->category}";
            $totals[$key] = round(($totals[$key] ?? 0) + (float) $line->amount, 2);
        }

        return $totals;
    }
}
