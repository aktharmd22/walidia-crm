<?php

declare(strict_types=1);

namespace App\Domain\Charter;

use App\Models\CharterEnquiry;
use App\Models\CharterMatch;
use App\Models\Yacht;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * The matching engine.
 *
 * Every score is explainable: the reasons are stored alongside it, so a broker
 * can defend a shortlist to a client rather than reciting a number nobody can
 * account for. Availability is a hard filter, not a scoring factor — offering
 * a yacht that is already out is worse than offering nothing.
 */
class CharterMatcher
{
    /**
     * @return Collection<int, CharterMatch>
     */
    public function match(CharterEnquiry $enquiry, int $limit = 10): Collection
    {
        $window = $this->window($enquiry);

        $candidates = Yacht::query()
            ->charterFleet()
            ->where('status', 'active')
            ->with(['charterProfile', 'homeMarina'])
            ->get()
            ->filter(fn (Yacht $yacht): bool => $yacht->charterProfile?->is_bookable !== false)
            ->filter(fn (Yacht $yacht): bool => $window === null
                || $yacht->isAvailableBetween($window['start'], $window['end']));

        $scored = $candidates
            ->map(fn (Yacht $yacht): array => $this->score($yacht, $enquiry))
            ->sortByDesc('score')
            ->take($limit);

        $enquiry->matches()->delete();

        $matches = new Collection;

        foreach ($scored as $row) {
            $matches->push($enquiry->matches()->create([
                'yacht_id' => $row['yacht']->getKey(),
                'score' => $row['score'],
                'reasons' => $row['reasons'],
            ]));
        }

        if ($enquiry->status === 'new') {
            $enquiry->forceFill(['status' => 'matching'])->save();
        }

        return $matches;
    }

    /**
     * @return array{yacht: Yacht, score: int, reasons: list<array{factor: string, detail: string, weight: int}>}
     */
    private function score(Yacht $yacht, CharterEnquiry $enquiry): array
    {
        $score = 50;
        $reasons = [];
        $guests = $enquiry->guestCount();

        // Capacity: comfortable fit scores; over capacity is disqualifying,
        // because cruising capacity is a licensing limit, not a preference.
        if ($guests > 0 && $yacht->capacity_cruising !== null) {
            if ($guests > $yacht->capacity_cruising) {
                return [
                    'yacht' => $yacht,
                    'score' => 0,
                    'reasons' => [[
                        'factor' => 'capacity',
                        'detail' => "Takes {$yacht->capacity_cruising} cruising; the enquiry is for {$guests}.",
                        'weight' => -50,
                    ]],
                ];
            }

            $headroom = $yacht->capacity_cruising - $guests;
            $weight = $headroom <= 4 ? 25 : ($headroom <= 10 ? 15 : 5);
            $score += $weight;
            $reasons[] = [
                'factor' => 'capacity',
                'detail' => "Comfortable for {$guests} of {$yacht->capacity_cruising} cruising guests.",
                'weight' => $weight,
            ];
        }

        // Budget: the day rate against what the client said they would spend.
        $rate = (float) ($yacht->charterProfile?->full_day_rate ?? 0);

        if ($rate > 0 && ($enquiry->budget_max !== null || $enquiry->budget_min !== null)) {
            $max = (float) ($enquiry->budget_max ?? $enquiry->budget_min);

            if ($rate <= $max) {
                $score += 20;
                $reasons[] = ['factor' => 'budget', 'detail' => 'Day rate is inside the stated budget.', 'weight' => 20];
            } elseif ($rate <= $max * 1.15) {
                $score += 5;
                $reasons[] = ['factor' => 'budget', 'detail' => 'Slightly above budget, within 15%.', 'weight' => 5];
            } else {
                $score -= 20;
                $reasons[] = ['factor' => 'budget', 'detail' => 'Materially above the stated budget.', 'weight' => -20];
            }
        }

        // Departure marina: a yacht already berthed where they want to leave
        // from saves a positioning charge and an argument about it.
        if ($enquiry->pickup_marina_id !== null && $yacht->home_marina_id === $enquiry->pickup_marina_id) {
            $score += 15;
            $reasons[] = [
                'factor' => 'marina',
                'detail' => 'Already berthed at the requested departure marina.',
                'weight' => 15,
            ];
        }

        if ($enquiry->yacht_preference_id === $yacht->getKey()) {
            $score += 30;
            $reasons[] = ['factor' => 'preference', 'detail' => 'The client asked for this yacht.', 'weight' => 30];
        }

        return ['yacht' => $yacht, 'score' => max(0, min(100, $score)), 'reasons' => $reasons];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}|null
     */
    private function window(CharterEnquiry $enquiry): ?array
    {
        if ($enquiry->requested_date === null) {
            return null;
        }

        $timezone = $enquiry->pickupMarina?->timezone ?? config('walidia.display_timezone');
        $start = CarbonImmutable::parse(
            $enquiry->requested_date->toDateString().' '.($enquiry->start_time ?? '10:00:00'),
            $timezone,
        )->setTimezone('UTC');

        return [
            'start' => $start,
            'end' => $start->addMinutes((int) round((float) ($enquiry->duration_hours ?? 4) * 60)),
        ];
    }
}
