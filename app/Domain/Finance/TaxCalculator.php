<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Models\VatRate;

/**
 * UAE VAT, as configuration rather than a hardcoded 5% (Q5).
 *
 * Treatment is decided per line, because one charter can legitimately carry a
 * standard-rated yacht fee, an out-of-scope security deposit and out-of-scope
 * crew tips on the same document. Everything is computed in integer fils and
 * rounded once, at the line — never on a running total (D-002).
 */
class TaxCalculator
{
    /** Categories that are not a supply at all, so never carry VAT. */
    public function isOutOfScope(string $category): bool
    {
        return in_array($category, (array) config('walidia.tax.out_of_scope_categories', []), true);
    }

    /**
     * The treatment that applies to a line, unless the user has overridden it.
     *
     * International charters — departing and returning outside the UAE — are
     * outside the scope of UAE VAT; this is the assumption the client's tax
     * advisor must confirm before Phase 3 ships (Q5).
     */
    public function treatmentFor(string $category, bool $isInternational = false): string
    {
        return match (true) {
            $this->isOutOfScope($category) => 'out_of_scope',
            $isInternational => 'out_of_scope',
            default => 'standard',
        };
    }

    public function rateFor(string $treatment): float
    {
        if ($treatment !== 'standard') {
            return 0.0;
        }

        $rate = VatRate::query()
            ->where('treatment', 'standard')
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', now()))
            ->orderByDesc('is_default')
            ->value('rate_pct');

        return (float) ($rate ?? config('walidia.tax.default_rate', 5));
    }

    /**
     * Tax on one line. Integer fils in, integer fils out, rounded half-up once.
     */
    public function taxOn(float $amount, string $treatment): float
    {
        $rate = $this->rateFor($treatment);

        if ($rate === 0.0) {
            return 0.0;
        }

        $fils = (int) round($amount * 100);
        $taxFils = (int) round($fils * $rate / 100);

        return $taxFils / 100;
    }

    /**
     * A whole line: amount, tax and total, consistently rounded.
     *
     * @return array{amount: float, tax_amount: float, line_total: float, tax_rate: float, tax_treatment: string}
     */
    public function line(float $quantity, float $unitPrice, string $category, bool $isInternational = false, ?string $treatmentOverride = null): array
    {
        $treatment = $treatmentOverride ?? $this->treatmentFor($category, $isInternational);

        $amountFils = (int) round($quantity * $unitPrice * 100);
        $amount = $amountFils / 100;
        $tax = $this->taxOn($amount, $treatment);

        return [
            'amount' => $amount,
            'tax_amount' => $tax,
            'line_total' => round($amount + $tax, 2),
            'tax_rate' => $this->rateFor($treatment),
            'tax_treatment' => $treatment,
        ];
    }
}
