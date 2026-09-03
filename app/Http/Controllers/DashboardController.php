<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Gates\GateEvaluator;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\CrewDocument;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Yacht;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * My Day.
 *
 * One screen that answers, in order: what did we earn, what is happening
 * today, what is blocked, and what expires soon. The ordering is not
 * decorative — it is how a director reads a morning, and a dashboard that
 * makes them hunt for the blocked charter is one they stop opening.
 *
 * Everything here is scoped by the same global scopes as the rest of the
 * system, so a Sales user's dashboard is their own book, not the company's.
 */
class DashboardController extends Controller
{
    public function myDay(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $today = CarbonImmutable::now(config('walidia.display_timezone'));
        $monthStart = $today->startOfMonth();

        // 3, 6 or 12 months — clamped, because it reaches a query.
        $months = (int) $request->integer('months', 12);
        $months = in_array($months, [3, 6, 12], true) ? $months : 12;

        return Inertia::render('Dashboard/MyDay', [
            'greeting' => $this->greeting($today),
            'today' => $today->toIso8601String(),
            'months' => $months,
            'metrics' => $this->metrics($monthStart, $today),
            'revenue' => $this->revenueByMonth($today, $months),
            'mix' => $this->revenueMix($monthStart),
            'team' => $this->teamOnTheWater($today),
            'sources' => $this->leadSources($monthStart),
            'charters' => $this->chartersThisWeek($today),
            'blockers' => $this->blockers($request),
            'tasks' => $this->tasks($request),
            'expiring' => $this->expiring($today),
        ]);
    }

    public function alerts(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $today = CarbonImmutable::now(config('walidia.display_timezone'));

        return Inertia::render('Dashboard/Alerts', [
            'hard' => $this->blockers($request),
            'soft' => [],
            'expiring' => $this->expiring($today, 90),
        ]);
    }

    public function calendar(Request $request): Response
    {
        $this->authorize('viewAny', Booking::class);

        $month = CarbonImmutable::parse(
            $request->query('month', CarbonImmutable::now(config('walidia.display_timezone'))->format('Y-m')).'-01',
        );

        $bookings = Booking::query()
            ->with(['yacht:id,name', 'client:id,full_name'])
            ->whereBetween('starts_at', [$month->startOfMonth(), $month->endOfMonth()])
            ->orderBy('starts_at')
            ->get();

        return Inertia::render('Dashboard/Calendar', [
            'month' => $month->format('Y-m'),
            'events' => $bookings->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'title' => $booking->yacht?->name ?? 'Charter',
                'subtitle' => $booking->client?->full_name,
                'starts_at' => $booking->starts_at->toIso8601String(),
                'ends_at' => $booking->ends_at->toIso8601String(),
                'status' => $booking->status,
                'tone' => $booking->statusTone(),
                'url' => route('charter.bookings.show', $booking->id),
            ])->all(),
        ]);
    }

    private function greeting(CarbonImmutable $today): string
    {
        return match (true) {
            $today->hour < 12 => 'Good morning',
            $today->hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    /**
     * The four figures at the top.
     *
     * Each carries its own change against the previous month, because a number
     * without a direction is trivia.
     *
     * @return list<array<string, mixed>>
     */
    private function metrics(CarbonImmutable $monthStart, CarbonImmutable $today): array
    {
        $previousStart = $monthStart->subMonth();

        $revenue = $this->clearedRevenue($monthStart, $today);
        $previousRevenue = $this->clearedRevenue($previousStart, $monthStart);

        $charters = Booking::whereBetween('starts_at', [$monthStart, $today->endOfMonth()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();
        $previousCharters = Booking::whereBetween('starts_at', [$previousStart, $monthStart])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        $fleet = max(Yacht::where('status', 'active')->count(), 1);
        $daysInMonth = max($today->daysInMonth, 1);
        $charterDays = (int) Booking::whereBetween('starts_at', [$monthStart, $today->endOfMonth()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();
        $utilisation = min((int) round($charterDays / ($fleet * $daysInMonth) * 100), 100);

        $averageValue = $charters > 0 ? $revenue / $charters : 0.0;
        $previousAverage = $previousCharters > 0 ? $previousRevenue / $previousCharters : 0.0;

        return [
            [
                'key' => 'revenue',
                'label' => 'Cleared revenue',
                'value' => number_format($revenue, 0),
                'prefix' => 'AED',
                'change' => $this->change($revenue, $previousRevenue),
                'icon' => 'finance',
                'tone' => 'accent',
                'spark' => $this->revenueSpark($today),
                'sparkVariant' => 'line',
            ],
            [
                'key' => 'charters',
                'label' => 'Charters this month',
                'value' => number_format($charters),
                'change' => $this->change((float) $charters, (float) $previousCharters),
                'icon' => 'charter',
                'tone' => 'info',
                'spark' => $this->charterSpark($today),
                'sparkVariant' => 'bar',
            ],
            [
                'key' => 'utilisation',
                'label' => 'Fleet utilisation',
                'value' => $utilisation.'%',
                'change' => null,
                'icon' => 'fleet',
                'tone' => 'success',
                'spark' => $this->charterSpark($today),
                'sparkVariant' => 'bar',
            ],
            [
                'key' => 'average',
                'label' => 'Average charter',
                'value' => number_format($averageValue, 0),
                'prefix' => 'AED',
                'change' => $this->change($averageValue, $previousAverage),
                'icon' => 'finance',
                'tone' => 'warning',
                'spark' => $this->revenueSpark($today),
                'sparkVariant' => 'line',
            ],
        ];
    }

    /**
     * @return array{value: float, direction: string}|null
     */
    private function change(float $current, float $previous): ?array
    {
        if ($previous <= 0.0) {
            return null;
        }

        $delta = ($current - $previous) / $previous * 100;

        return [
            'value' => round(abs($delta), 1),
            'direction' => $delta >= 0 ? 'up' : 'down',
        ];
    }

    /** Money that actually arrived, not money that was invoiced. */
    private function clearedRevenue(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) Payment::query()
            ->whereNotNull('cleared_at')
            ->whereBetween('cleared_at', [$from, $to])
            ->sum('amount_aed');
    }

    /**
     * @return list<int>
     */
    private function revenueSpark(CarbonImmutable $today): array
    {
        return $this->monthlyTotals($today, 6, fn (CarbonImmutable $from, CarbonImmutable $to): float => $this->clearedRevenue($from, $to));
    }

    /**
     * @return list<int>
     */
    private function charterSpark(CarbonImmutable $today): array
    {
        return $this->monthlyTotals($today, 6, fn (CarbonImmutable $from, CarbonImmutable $to): float => (float) Booking::query()
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count());
    }

    /**
     * @param  callable(CarbonImmutable, CarbonImmutable): float  $total
     * @return list<int>
     */
    private function monthlyTotals(CarbonImmutable $today, int $months, callable $total): array
    {
        $values = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $from = $today->startOfMonth()->subMonths($offset);
            $values[] = (int) round($total($from, $from->endOfMonth()));
        }

        return $values;
    }

    /**
     * Twelve months of revenue, split by the three business lines, so the
     * question "which line is carrying us?" answers itself.
     *
     * @return list<array<string, mixed>>
     */
    private function revenueByMonth(CarbonImmutable $today, int $months = 12): array
    {
        $rows = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $from = $today->startOfMonth()->subMonths($offset);
            $to = $from->endOfMonth();

            $rows[] = [
                'label' => $from->format('M'),
                'charter' => (int) round((float) Payment::query()
                    ->whereNotNull('cleared_at')
                    ->whereBetween('cleared_at', [$from, $to])
                    ->sum('amount_aed')),
                'brokerage' => (int) round((float) Transaction::query()
                    ->whereNotNull('ownership_transferred_at')
                    ->whereBetween('ownership_transferred_at', [$from, $to])
                    ->sum('agreed_price')),
                'management' => (int) round((float) Invoice::query()
                    ->whereNotNull('issued_at')
                    ->whereBetween('issued_at', [$from, $to])
                    ->sum('total')),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function revenueMix(CarbonImmutable $monthStart): array
    {
        $charter = (float) Payment::whereNotNull('cleared_at')->where('cleared_at', '>=', $monthStart)->sum('amount_aed');
        $brokerage = (float) Transaction::whereNotNull('ownership_transferred_at')
            ->where('ownership_transferred_at', '>=', $monthStart)
            ->sum('agreed_price');
        $management = (float) Invoice::whereNotNull('issued_at')->where('issued_at', '>=', $monthStart)->sum('total');

        return [
            ['name' => 'Charter', 'value' => (int) round($charter), 'tone' => 'accent'],
            ['name' => 'Brokerage', 'value' => (int) round($brokerage), 'tone' => 'info'],
            ['name' => 'Management', 'value' => (int) round($management), 'tone' => 'success'],
        ];
    }

    /**
     * The people behind this month's charters.
     *
     * A dashboard that only shows money forgets that someone is delivering it;
     * the stack is a quiet reminder of who is carrying the month.
     *
     * @return array{avatars: list<array{name: string, avatar: string|null}>, more: int}
     */
    private function teamOnTheWater(CarbonImmutable $today): array
    {
        $users = User::query()
            ->whereIn('id', Booking::query()
                ->whereBetween('starts_at', [$today->startOfMonth(), $today->endOfMonth()])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->whereNotNull('assigned_user_id')
                ->distinct()
                ->pluck('assigned_user_id'))
            ->limit(9)
            ->get();

        return [
            'avatars' => $users->take(4)->map(fn (User $user): array => [
                'name' => $user->name,
                'avatar' => $user->avatarUrl(),
            ])->all(),
            'more' => max($users->count() - 4, 0),
        ];
    }

    /**
     * Where the work is coming from. The bar does the comparing.
     *
     * @return list<array<string, mixed>>
     */
    private function leadSources(CarbonImmutable $monthStart): array
    {
        /** @var Collection<int, object{name: string|null, total: int}> $rows */
        $rows = Lead::query()
            ->selectRaw('lead_sources.name as name, count(*) as total')
            ->leftJoin('lead_sources', 'leads.source_id', '=', 'lead_sources.id')
            ->where('leads.created_at', '>=', $monthStart->subMonths(2))
            ->groupBy('lead_sources.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $highest = max((int) $rows->max('total'), 1);

        return $rows->map(fn (object $row): array => [
            'name' => $row->name ?? 'Direct',
            'total' => (int) $row->total,
            'share' => (int) round((int) $row->total / $highest * 100),
        ])->all();
    }

    /**
     * The week ahead, which is the part of the dashboard the operations team
     * actually looks at.
     *
     * @return list<array<string, mixed>>
     */
    private function chartersThisWeek(CarbonImmutable $today): array
    {
        return Booking::query()
            ->with(['yacht.media', 'client:id,full_name'])
            ->whereBetween('starts_at', [$today->startOfDay(), $today->addDays(7)->endOfDay()])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at')
            ->limit(8)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'yacht' => $booking->yacht?->name,
                'thumbnail' => $booking->yacht?->heroImageUrl(),
                'client' => $booking->client?->full_name,
                'starts_at' => $booking->starts_at->toIso8601String(),
                'guests' => $booking->guestCount(),
                'status' => $booking->status,
                'tone' => $booking->statusTone(),
                'released' => $booking->isReleased(),
                'url' => route('charter.bookings.show', $booking->id),
            ])->all();
    }

    /**
     * What is blocked, and by what.
     *
     * The gate engine is asked rather than second-guessed, so this list and
     * the button on the record can never disagree.
     *
     * @return list<array<string, mixed>>
     */
    private function blockers(Request $request): array
    {
        $gates = app(GateEvaluator::class);
        $user = $request->user();

        $blocked = [];

        $candidates = Booking::query()
            ->with(['yacht:id,name', 'client:id,full_name'])
            ->whereIn('status', ['confirmed', 'deposit_pending', 'contract_signed', 'pending_contract'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(20)
            ->get();

        foreach ($candidates as $booking) {
            $gate = $gates->forAction($booking, 'bookings.release-operations', $user);

            if ($gate->verdict !== 'block') {
                continue;
            }

            $blocked[] = [
                'id' => $booking->id,
                'title' => $booking->yacht?->name ?? 'Charter',
                'subtitle' => $booking->reference,
                'starts_at' => $booking->starts_at->toIso8601String(),
                'reasons' => array_map(fn ($failure): string => $failure->message, $gate->failures),
                'url' => route('charter.bookings.show', $booking->id),
            ];

            if (count($blocked) === 6) {
                break;
            }
        }

        return $blocked;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tasks(Request $request): array
    {
        return Task::query()
            ->where('status', 'open')
            ->where(fn ($query) => $query
                ->where('assigned_user_id', $request->user()->id)
                ->orWhereNull('assigned_user_id'))
            ->orderByRaw('due_at is null, due_at')
            ->limit(8)
            ->get()
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'due_at' => $task->due_at?->toIso8601String(),
                'overdue' => $task->due_at !== null && $task->due_at->isPast(),
                'priority' => $task->priority,
                'url' => route('tasks.show', $task->id),
            ])->all();
    }

    /**
     * Paperwork with a death date — the quiet way a charter gets cancelled on
     * the morning.
     *
     * @return list<array<string, mixed>>
     */
    private function expiring(CarbonImmutable $today, int $days = 45): array
    {
        $rows = [];

        foreach (Certificate::query()
            ->with('yacht:id,name')
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', $today->addDays($days))
            ->orderBy('expires_on')
            ->limit(6)
            ->get() as $certificate) {
            $rows[] = [
                'kind' => 'Certificate',
                'title' => $certificate->name,
                'subtitle' => $certificate->yacht?->name,
                'expires_on' => $certificate->expires_on?->toDateString(),
                'expired' => $certificate->isExpired(),
                'blocking' => $certificate->blocks_charter,
                'url' => route('management.certificates.show', $certificate->id),
            ];
        }

        foreach (CrewDocument::query()
            ->with('crew:id,full_name')
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', $today->addDays($days))
            ->orderBy('expires_on')
            ->limit(6)
            ->get() as $document) {
            $rows[] = [
                'kind' => 'Crew document',
                'title' => str_replace('_', ' ', $document->type),
                'subtitle' => $document->crew?->full_name,
                'expires_on' => $document->expires_on?->toDateString(),
                'expired' => $document->isExpired(),
                'blocking' => true,
                'url' => $document->crew_id === null ? null : route('crew.show', $document->crew_id),
            ];
        }

        foreach (Listing::query()
            ->with('yacht:id,name')
            ->whereNotNull('agreement_expires_on')
            ->where('status', 'active')
            ->whereDate('agreement_expires_on', '<=', $today->addDays($days))
            ->orderBy('agreement_expires_on')
            ->limit(4)
            ->get() as $listing) {
            $rows[] = [
                'kind' => 'Listing mandate',
                'title' => $listing->yacht?->name ?? 'Listing',
                'subtitle' => $listing->reference,
                'expires_on' => $listing->agreement_expires_on?->toDateString(),
                'expired' => ! $listing->agreementIsActive(),
                'blocking' => false,
                'url' => route('brokerage.listings.show', $listing->id),
            ];
        }

        usort($rows, fn (array $a, array $b): int => ($a['expires_on'] ?? '9999') <=> ($b['expires_on'] ?? '9999'));

        return array_slice($rows, 0, 8);
    }
}
