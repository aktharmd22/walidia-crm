<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Automation\WorkflowEngine;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Listing;
use App\Models\PaymentScheduleItem;
use App\Models\Transaction;
use App\Models\WorkflowRule;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * The heartbeat.
 *
 * Two jobs, in order: queue what the clock now says is due, then dispatch what
 * has come due. Splitting them means a scheduling bug can be fixed and re-run
 * without sending anything, and a sending outage can be retried without
 * re-queueing.
 */
class RunAutomation extends Command
{
    protected $signature = 'walidia:automation
                            {--queue-only : Schedule due work without sending}
                            {--limit=200 : How many runs to dispatch}';

    protected $description = 'Queue and dispatch the automation rules that have come due';

    /** Which model each scheduled rule scans, keyed by the field it anchors on. */
    private const SCHEDULED_SUBJECTS = [
        'starts_at' => Booking::class,
        'ends_at' => Booking::class,
        'due_at' => PaymentScheduleItem::class,
        'agreement_expires_on' => Listing::class,
        'ownership_transferred_at' => Transaction::class,
        'date_of_birth' => Client::class,
    ];

    public function handle(WorkflowEngine $engine): int
    {
        $queued = $this->queueScheduled($engine);
        $this->info("Queued {$queued} scheduled runs.");

        if ($this->option('queue-only')) {
            return self::SUCCESS;
        }

        $tally = $engine->runDue((int) $this->option('limit'));

        $this->info(sprintf(
            'Dispatched: %d sent, %d skipped, %d failed.',
            $tally['sent'],
            $tally['skipped'],
            $tally['failed'],
        ));

        return $tally['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Walk the schedule-triggered rules and queue anything whose moment is
     * within reach. The window is deliberately generous on the past side:
     * a scheduler that missed a night should catch up, not skip a day.
     */
    private function queueScheduled(WorkflowEngine $engine): int
    {
        $queued = 0;

        $rules = WorkflowRule::query()
            ->where('trigger_type', 'schedule')
            ->where('is_active', true)
            ->whereNotNull('anchor_field')
            ->get();

        foreach ($rules as $rule) {
            $model = self::SCHEDULED_SUBJECTS[$rule->anchor_field] ?? null;

            if ($model === null) {
                continue;
            }

            // The anchor date that would make this rule due between yesterday
            // and tomorrow.
            $from = now()->subDay()->subHours($rule->offset_hours);
            $to = now()->addDay()->subHours($rule->offset_hours);

            $query = $model::query()->whereNotNull($rule->anchor_field);

            if ($rule->recurrence === 'annual') {
                /*
                 * An anniversary is a day of the year, not a date. Comparing a
                 * birth date to a window around today would never match, so
                 * match the month and the day and let the year fall away.
                 */
                // Today's date, not the window's start: an anniversary falls
                // on one day, and $from is deliberately a day behind it.
                $query
                    ->whereMonth($rule->anchor_field, now()->month)
                    ->whereDay($rule->anchor_field, now()->day);
            } else {
                $query->whereBetween($rule->anchor_field, [$from, $to]);
            }

            $occurrence = $rule->recurrence === 'annual' ? (string) now()->year : null;

            $query->each(function (Model $subject) use ($engine, $rule, $occurrence, &$queued): void {
                if ($engine->applies($rule, $subject) && $engine->schedule($rule, $subject, $occurrence) !== null) {
                    $queued++;
                }
            });
        }

        return $queued;
    }
}
