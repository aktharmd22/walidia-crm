<?php

declare(strict_types=1);

namespace App\Domain\Brokerage;

use App\Models\BuyerRequirement;
use App\Models\Listing;
use Illuminate\Support\Collection;

/**
 * Matching a buyer's brief to the listings we can actually sell.
 *
 * The same principle as the charter matcher: budget is a hard filter and
 * everything else is a weighted, explainable score. A broker will not put a
 * yacht in front of a UHNW buyer on the strength of a number they cannot
 * account for, so every match carries its reasons.
 *
 * One difference from charter matching is worth stating. A charter yacht is
 * either free on the date or it is not; a listing under offer is still worth
 * showing, because offers fall through — it is scored down, not filtered out.
 */
class ListingMatcher
{
    /** Budget tolerance: a buyer at 10M will look at 11M, not at 20M. */
    private const BUDGET_STRETCH = 1.10;

    /**
     * @return Collection<int, array{listing: Listing, score: int, reasons: list<array{factor: string, detail: string, weight: int}>}>
     */
    public function match(BuyerRequirement $requirement, int $limit = 10): Collection
    {
        return Listing::query()
            ->with(['yacht', 'yacht.saleProfile'])
            ->whereIn('status', ['active', 'under_offer'])
            ->where('is_published', true)
            ->get()
            // Hard filter: a listing whose mandate has lapsed is not ours to sell.
            ->filter(fn (Listing $listing): bool => $listing->agreementIsActive())
            ->filter(fn (Listing $listing): bool => $this->withinBudget($listing, $requirement))
            ->map(fn (Listing $listing): array => $this->score($listing, $requirement))
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    private function withinBudget(Listing $listing, BuyerRequirement $requirement): bool
    {
        $asking = (float) $listing->asking_price;

        if ($requirement->budget_max !== null && $asking > (float) $requirement->budget_max * self::BUDGET_STRETCH) {
            return false;
        }

        // A floor matters too: showing a 4M boat to a 12M buyer reads as not
        // having listened.
        return $requirement->budget_min === null || $asking >= (float) $requirement->budget_min * 0.75;
    }

    /**
     * @return array{listing: Listing, score: int, reasons: list<array{factor: string, detail: string, weight: int}>}
     */
    private function score(Listing $listing, BuyerRequirement $requirement): array
    {
        $score = 0;
        $reasons = [];
        $yacht = $listing->yacht;
        $asking = (float) $listing->asking_price;

        /* Budget — the closer to the top of the range, the better the fit. */
        if ($requirement->budget_max !== null) {
            $ratio = $asking / max((float) $requirement->budget_max, 1);

            [$weight, $detail] = match (true) {
                $ratio <= 1.0 => [30, 'Inside budget.'],
                $ratio <= self::BUDGET_STRETCH => [18, 'Slightly above budget — negotiable.'],
                default => [0, 'Above budget.'],
            };

            $score += $weight;
            $reasons[] = ['factor' => 'budget', 'detail' => $detail, 'weight' => $weight];
        }

        /* Length — the single specification buyers are most rigid about. */
        if ($yacht?->loa_m !== null && ($requirement->loa_min !== null || $requirement->loa_max !== null)) {
            $loa = (float) $yacht->loa_m;
            $min = (float) ($requirement->loa_min ?? 0);
            $max = (float) ($requirement->loa_max ?? 999);

            $weight = $loa >= $min && $loa <= $max ? 25 : 0;
            $score += $weight;

            $reasons[] = [
                'factor' => 'length',
                'detail' => $weight > 0
                    ? sprintf('%.1fm, inside the %g–%gm brief.', $loa, $min, $max)
                    : sprintf('%.1fm, outside the %g–%gm brief.', $loa, $min, $max),
                'weight' => $weight,
            ];
        }

        /* Builder — a named preference is a strong signal when it lands. */
        $builders = $requirement->preferred_builders ?? [];

        if ($builders !== [] && $yacht?->builder !== null) {
            $hit = false;

            foreach ($builders as $builder) {
                if (stripos((string) $yacht->builder, (string) $builder) !== false) {
                    $hit = true;
                    break;
                }
            }

            $weight = $hit ? 20 : 0;
            $score += $weight;

            $reasons[] = [
                'factor' => 'builder',
                'detail' => $hit ? "{$yacht->builder} is on the buyer's list." : "{$yacht->builder} is not a named preference.",
                'weight' => $weight,
            ];
        }

        /* Age — newer scores higher, but only within the buyer's own floor. */
        if ($requirement->year_from !== null && $yacht?->year_built !== null) {
            $weight = $yacht->year_built >= $requirement->year_from ? 15 : 0;
            $score += $weight;

            $reasons[] = [
                'factor' => 'year',
                'detail' => $weight > 0
                    ? "Built {$yacht->year_built}, at or after the buyer's floor of {$requirement->year_from}."
                    : "Built {$yacht->year_built}, older than the buyer's floor of {$requirement->year_from}.",
                'weight' => $weight,
            ];
        }

        /* Availability of the sale itself. */
        if ($listing->status === 'under_offer') {
            $reasons[] = [
                'factor' => 'status',
                'detail' => 'Under offer — worth showing, since offers fall through.',
                'weight' => -10,
            ];
            $score -= 10;
        }

        /* A central mandate is one we can actually transact quickly. */
        if ($listing->mandate_type === 'central') {
            $score += 10;
            $reasons[] = ['factor' => 'mandate', 'detail' => 'Central agency — we control the sale.', 'weight' => 10];
        }

        return ['listing' => $listing, 'score' => max($score, 0), 'reasons' => $reasons];
    }
}
