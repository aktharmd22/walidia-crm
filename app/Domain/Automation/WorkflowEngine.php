<?php

declare(strict_types=1);

namespace App\Domain\Automation;

use App\Models\MessageTemplate;
use App\Models\Task;
use App\Models\WorkflowRule;
use App\Models\WorkflowRun;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The automation engine.
 *
 * Deliberately built like the gate engine: rules are rows, the engine reads
 * them, and every firing is written down. Three properties follow from that,
 * and each one exists because of a way automation normally goes wrong.
 *
 * 1. Nothing sends twice. A run is unique on (rule, subject), so a rule that
 *    is re-evaluated — because a booking was edited, or a queue worker
 *    restarted — does not send the client a second thank-you.
 *
 * 2. Nothing sends silently. A skip is recorded with its reason, so "why
 *    didn't the client get the reminder?" has an answer that is not a shrug.
 *
 * 3. Nothing sends by accident. Scheduling only queues; `run()` is what
 *    dispatches, and a rule that is inactive when its moment arrives is
 *    skipped rather than fired late.
 */
class WorkflowEngine
{
    public function __construct(private readonly MessageDispatcher $messages) {}

    /**
     * Something happened. Queue every rule listening for it.
     *
     * @return int the number of runs queued
     */
    public function fire(string $event, Model $subject): int
    {
        $rules = WorkflowRule::query()
            ->where('trigger_type', 'event')
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $queued = 0;

        foreach ($rules as $rule) {
            if (! $this->applies($rule, $subject)) {
                continue;
            }

            $queued += $this->schedule($rule, $subject) === null ? 0 : 1;
        }

        return $queued;
    }

    /**
     * Queue one rule against one record, unless it is already queued.
     *
     * The uniqueness is enforced by the database, not by this check, so two
     * workers racing cannot both win.
     */
    /**
     * @param  string|null  $occurrence  Distinguishes one turn of a recurring
     *                                   rule from the next — the year, for an
     *                                   annual one. A one-shot rule stores the
     *                                   literal 'once' rather than null,
     *                                   because two nulls do not collide in a
     *                                   unique index and the whole guarantee
     *                                   rests on that collision.
     */
    public function schedule(WorkflowRule $rule, Model $subject, ?string $occurrence = null): ?WorkflowRun
    {
        $anchor = $this->anchor($rule, $subject);

        if ($anchor === null) {
            return null;
        }

        // An anniversary's anchor is in the past; what falls due is this
        // year's turn of it.
        if ($rule->recurrence === 'annual') {
            $anchor = $anchor->setYear((int) now()->year);
        }

        try {
            return WorkflowRun::create([
                'workflow_rule_id' => $rule->getKey(),
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'occurrence_key' => $occurrence ?? 'once',
                'due_at' => $rule->dueAt($anchor),
                'status' => 'pending',
            ]);
        } catch (Throwable) {
            // Already queued. That is the desired outcome, not an error.
            return null;
        }
    }

    /**
     * Dispatch everything that has come due.
     *
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function runDue(int $limit = 200): array
    {
        $tally = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        $runs = WorkflowRun::query()
            ->with(['rule.template'])
            ->where('status', 'pending')
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->limit($limit)
            ->get();

        foreach ($runs as $run) {
            $outcome = $this->run($run);
            $tally[$outcome]++;
        }

        return $tally;
    }

    /**
     * One run. Returns 'sent', 'skipped' or 'failed'.
     */
    public function run(WorkflowRun $run): string
    {
        $rule = $run->rule;
        $subject = $run->subject;

        if ($rule === null || ! $rule->is_active) {
            return $this->skip($run, 'The rule is no longer active.');
        }

        if ($subject === null) {
            return $this->skip($run, 'The record no longer exists.');
        }

        // Conditions are re-checked at send time, not only at queue time: a
        // charter cancelled after the reminder was queued must not be reminded.
        if (! $this->applies($rule, $subject)) {
            return $this->skip($run, 'Conditions no longer hold.');
        }

        try {
            DB::transaction(function () use ($rule, $subject, $run): void {
                match ($rule->action) {
                    'send_message' => $this->sendMessage($rule, $subject, $run),
                    'create_task' => $this->createTask($rule, $subject),
                    'notify_role' => $this->sendMessage($rule, $subject, $run),
                    'update_field' => $this->updateField($rule, $subject),
                    default => throw new \RuntimeException("Unknown action [{$rule->action}]."),
                };

                $run->forceFill(['status' => 'sent', 'ran_at' => now()])->save();
            });

            return 'sent';
        } catch (Throwable $exception) {
            // The message, never the payload: client PII does not reach a log.
            Log::warning('Workflow run failed', [
                'run' => $run->getKey(),
                'rule' => $rule->key,
                'error' => $exception->getMessage(),
            ]);

            $run->forceFill([
                'status' => 'failed',
                'ran_at' => now(),
                'error' => $exception->getMessage(),
            ])->save();

            return 'failed';
        }
    }

    /**
     * Whether a rule's conditions hold for a record.
     *
     * Conditions are simple field comparisons by design. Anything that needs
     * real logic belongs in the gate engine, which is built for it and audited
     * accordingly.
     */
    public function applies(WorkflowRule $rule, Model $subject): bool
    {
        if ($rule->subject_type !== null && $rule->subject_type !== $subject->getMorphClass()) {
            return false;
        }

        foreach ($rule->conditions ?? [] as $condition) {
            $field = $condition['field'] ?? null;

            if ($field === null) {
                continue;
            }

            $actual = $subject->getAttribute($field);
            $expected = $condition['value'] ?? null;

            $holds = match ($condition['operator'] ?? 'equals') {
                'equals' => $actual == $expected,
                'not_equals' => $actual != $expected,
                'in' => in_array($actual, (array) $expected, false),
                'not_in' => ! in_array($actual, (array) $expected, false),
                'is_null' => $actual === null,
                'not_null' => $actual !== null,
                default => true,
            };

            if (! $holds) {
                return false;
            }
        }

        return true;
    }

    private function anchor(WorkflowRule $rule, Model $subject): ?CarbonInterface
    {
        if ($rule->anchor_field === null) {
            return now();
        }

        $value = $subject->getAttribute($rule->anchor_field);

        return $value instanceof CarbonInterface ? $value : null;
    }

    private function sendMessage(WorkflowRule $rule, Model $subject, WorkflowRun $run): void
    {
        $template = $rule->template;

        if (! $template instanceof MessageTemplate) {
            throw new \RuntimeException("Rule [{$rule->key}] sends a message but has no template.");
        }

        $this->messages->send($template, $subject, $rule->audience, $run);
    }

    private function createTask(WorkflowRule $rule, Model $subject): void
    {
        $params = $rule->action_params ?? [];

        Task::create([
            'title' => (string) ($params['title'] ?? $rule->name),
            'type' => (string) ($params['type'] ?? 'next_action'),
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'due_at' => now()->addHours((int) ($params['due_in_hours'] ?? 24)),
            'priority' => (string) ($params['priority'] ?? 'normal'),
            'status' => 'open',
            'assigned_user_id' => $subject->getAttribute('assigned_user_id'),
        ]);
    }

    private function updateField(WorkflowRule $rule, Model $subject): void
    {
        $params = $rule->action_params ?? [];
        $field = $params['field'] ?? null;

        if ($field === null) {
            throw new \RuntimeException("Rule [{$rule->key}] updates a field but names none.");
        }

        $subject->forceFill([$field => $params['value'] ?? null])->save();
    }

    private function skip(WorkflowRun $run, string $reason): string
    {
        $run->forceFill([
            'status' => 'skipped',
            'ran_at' => now(),
            'skip_reason' => $reason,
        ])->save();

        return 'skipped';
    }
}
