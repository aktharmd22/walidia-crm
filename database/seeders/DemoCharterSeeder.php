<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Client;
use App\Models\CostSheet;
use App\Models\Marina;
use App\Models\Payment;
use App\Models\User;
use App\Models\Yacht;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * A charter book.
 *
 * The demo data had clients, yachts and leads but no bookings at all, so every
 * figure on the dashboard was zero and the design could not be judged. This
 * fills a year behind and a fortnight ahead.
 *
 * The shape is chosen to exercise the screens rather than to look tidy:
 * completed charters with cleared payments so revenue and the twelve-month
 * chart have something to draw, confirmed charters in the week ahead, a couple
 * still awaiting a deposit so the blocked list and the release gate have real
 * subjects, and one cancellation so the status palette is not all green.
 */
class DemoCharterSeeder extends Seeder
{
    public function run(): void
    {
        $yachts = Yacht::query()->whereHas('charterProfile')->get();
        $clients = Client::query()->limit(30)->get();
        $marinas = Marina::query()->limit(4)->get();
        $agents = User::query()->limit(4)->get();

        if ($yachts->isEmpty() || $clients->isEmpty()) {
            $this->command?->warn('Seed yachts and clients first — DemoDataSeeder.');

            return;
        }

        if (Booking::query()->exists()) {
            $this->command?->info('Charters already seeded; leaving them alone.');

            return;
        }

        $today = CarbonImmutable::now(config('walidia.display_timezone'));
        $created = 0;
        $cleared = 0;

        /* ── A year of completed charters ─────────────────────────────────── */

        for ($monthsBack = 11; $monthsBack >= 1; $monthsBack--) {
            $month = $today->startOfMonth()->subMonths($monthsBack);

            // Busier in the cool season, quiet through the Gulf summer.
            $count = in_array($month->month, [6, 7, 8], true) ? 2 : random_int(4, 7);

            for ($index = 0; $index < $count; $index++) {
                $starts = $month->addDays(random_int(0, 26))->setTime(random_int(9, 16), 0);

                $booking = $this->makeBooking($yachts, $clients, $marinas, $agents, $starts, 'completed');
                $booking->forceFill(['completed_at' => $booking->ends_at])->save();

                $value = $this->valueFor($booking);
                $this->settle($booking, $value, $starts->subDays(random_int(3, 20)));

                $created++;
                $cleared++;
            }
        }

        /* ── This month, so the KPI cards have a figure ───────────────────── */

        foreach (range(1, 5) as $index) {
            $starts = $today->startOfMonth()->addDays(random_int(0, max($today->day - 1, 1)))->setTime(11, 0);
            $booking = $this->makeBooking($yachts, $clients, $marinas, $agents, $starts, 'completed');
            $booking->forceFill(['completed_at' => $booking->ends_at])->save();

            $this->settle($booking, $this->valueFor($booking), $starts->subDays(5));
            $created++;
            $cleared++;
        }

        /* ── The week ahead ───────────────────────────────────────────────── */

        foreach ([1, 2, 3, 4, 5, 6] as $daysAhead) {
            $starts = $today->addDays($daysAhead)->setTime(random_int(10, 17), 0);

            // Two of them are still waiting on money, which is what the release
            // gate exists to catch — the dashboard should show them blocked.
            $released = $daysAhead % 3 !== 0;

            $booking = $this->makeBooking(
                $yachts,
                $clients,
                $marinas,
                $agents,
                $starts,
                $released ? 'confirmed' : 'deposit_pending',
            );

            if ($released) {
                $booking->forceFill([
                    'operational_release_at' => $today->subDay(),
                    'operational_release_by' => $agents->first()?->id,
                ])->save();

                $this->settle($booking, $this->valueFor($booking), $today->subDays(4), close: false);
                $cleared++;
            }

            $created++;
        }

        /* ── Further out, and one that fell over ──────────────────────────── */

        foreach ([9, 14, 21, 30] as $daysAhead) {
            $this->makeBooking(
                $yachts,
                $clients,
                $marinas,
                $agents,
                $today->addDays($daysAhead)->setTime(12, 0),
                $daysAhead > 20 ? 'tentative' : 'confirmed',
            );
            $created++;
        }

        $cancelled = $this->makeBooking($yachts, $clients, $marinas, $agents, $today->addDays(11)->setTime(15, 0), 'cancelled');
        $cancelled->forceFill([
            'cancelled_at' => $today->subDays(2),
            'cancellation_reason' => 'Client postponed to the winter season.',
        ])->save();
        $created++;

        $this->command?->info("Seeded {$created} charters, {$cleared} with cleared payments.");
    }

    /**
     * @param  Collection<int, Yacht>  $yachts
     * @param  Collection<int, Client>  $clients
     * @param  Collection<int, Marina>  $marinas
     * @param  Collection<int, User>  $agents
     */
    private function makeBooking(
        $yachts,
        $clients,
        $marinas,
        $agents,
        CarbonImmutable $starts,
        string $status,
    ): Booking {
        $hours = [4, 6, 8, 8, 10][random_int(0, 4)];

        return Booking::create([
            'client_id' => $clients->random()->id,
            'yacht_id' => $yachts->random()->id,
            'starts_at' => $starts,
            'ends_at' => $starts->addHours($hours),
            'departure_marina_id' => $marinas->isEmpty() ? null : $marinas->random()->id,
            'return_marina_id' => $marinas->isEmpty() ? null : $marinas->random()->id,
            'guests_adults' => random_int(6, 24),
            'guests_children' => random_int(0, 4),
            'currency' => 'AED',
            'status' => $status,
            'assigned_user_id' => $agents->isEmpty() ? null : $agents->random()->id,
        ]);
    }

    /** Roughly what a charter of this length on this yacht would be sold for. */
    private function valueFor(Booking $booking): float
    {
        $hourly = (float) ($booking->yacht?->charterProfile?->hourly_rate ?? 3500);
        $hours = max($booking->starts_at->diffInHours($booking->ends_at), 1);

        return round($hourly * $hours * (1 + random_int(0, 35) / 100), 2);
    }

    /** The money, and the cost sheet that explains what was left of it. */
    private function settle(Booking $booking, float $value, CarbonImmutable $receivedAt, bool $close = true): void
    {
        Payment::create([
            'client_id' => $booking->client_id,
            'method' => ['bank_transfer', 'card', 'bank_transfer', 'cheque'][random_int(0, 3)],
            'amount' => $value,
            'currency' => 'AED',
            'exchange_rate' => 1,
            'amount_aed' => $value,
            'received_at' => $receivedAt,
            'cleared_at' => $receivedAt->addDays(random_int(1, 3)),
            'status' => 'cleared',
        ]);

        $cost = round($value * (0.52 + random_int(0, 18) / 100), 2);

        CostSheet::create([
            'booking_id' => $booking->id,
            'currency' => 'AED',
            'exchange_rate' => 1,
            'total_offer' => $value,
            'total_cost' => $cost,
            'total_profit' => round($value - $cost, 2),
            'margin_pct' => round(($value - $cost) / max($value, 1) * 100, 2),
            // Financial closure follows the charter. An upcoming booking keeps
            // an open sheet, or the policy quite rightly refuses to let anyone
            // edit a charter that has not happened yet.
            'status' => $close ? 'closed' : 'open',
        ]);
    }
}
