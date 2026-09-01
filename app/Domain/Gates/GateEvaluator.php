<?php

declare(strict_types=1);

namespace App\Domain\Gates;

use App\Domain\Gates\Exceptions\GateBlockedException;
use App\Models\GateEvaluation;
use App\Models\GateOverride;
use App\Models\GateRule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * "What unlocks what", as an engine rather than scattered conditionals (D-004).
 *
 * Rules are data. This service loads the ones that apply to a subject and a
 * trigger, resolves each condition against the check registry, records the
 * evaluation — pass or fail — and returns one GateResult that the controller,
 * the screen and the tests all read.
 */
class GateEvaluator
{
    public function __construct(private readonly GateCheckRegistry $checks) {}

    /**
     * Evaluate an action, e.g. 'bookings.release-operations'.
     *
     * @param  array<string, mixed>  $context
     */
    public function forAction(Model $subject, string $action, ?User $user = null, array $context = []): GateResult
    {
        return $this->evaluate($subject, 'action', $action, null, $user, $context);
    }

    /**
     * Evaluate a state transition, e.g. status draft → confirmed.
     *
     * @param  array<string, mixed>  $context
     */
    public function forTransition(Model $subject, string $field, string $to, ?User $user = null, array $context = []): GateResult
    {
        return $this->evaluate($subject, 'transition', null, [
            'field' => $field,
            'to' => $to,
            // Where it is moving from, so a rule can guard one edge rather
            // than every route into a state.
            'from' => (string) ($subject->getAttribute($field) ?? '*'),
        ], $user, $context);
    }

    /**
     * The same evaluation, but it throws instead of returning — for the write
     * path, where proceeding past a blocked gate must be impossible.
     *
     * @param  array<string, mixed>  $context
     *
     * @throws GateBlockedException
     */
    public function assertAction(Model $subject, string $action, ?User $user = null, array $context = []): GateResult
    {
        $result = $this->forAction($subject, $action, $user, $context);

        if ($result->blocked()) {
            throw new GateBlockedException($result);
        }

        return $result;
    }

    /**
     * Proceed past a hard gate, deliberately and on the record.
     *
     * The override is written before the transition runs, so a failure halfway
     * through still leaves the decision visible in the register.
     *
     * @param  array<string, mixed>  $context
     */
    public function override(Model $subject, string $action, User $user, string $reason, array $context = []): GateResult
    {
        $result = $this->forAction($subject, $action, $user, $context);

        if (! $result->blocked()) {
            return $result;
        }

        abort_unless($result->overridable, 403, 'This gate cannot be overridden.');
        abort_unless($user->can('gates.override'), 403);

        $minimum = (int) config('walidia.gates.override_reason_min_length', 20);
        abort_if(mb_strlen(trim($reason)) < $minimum, 422, "Give a reason of at least {$minimum} characters.");

        foreach ($result->failures as $failure) {
            GateOverride::create([
                'gate_rule_id' => GateRule::where('key', $failure->rule)->value('id'),
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'user_id' => $user->id,
                'reason' => $reason,
                'failed_conditions' => [$failure->toArray()],
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        }

        if (method_exists($subject, 'logActivity')) {
            $subject->logActivity('gate', "Gate overridden: {$action}", $reason);
        }

        return GateResult::pass();
    }

    /**
     * @param  array{field: string, to: string, from: string}|null  $transition
     * @param  array<string, mixed>  $context
     */
    private function evaluate(
        Model $subject,
        string $triggerType,
        ?string $action,
        ?array $transition,
        ?User $user,
        array $context,
    ): GateResult {
        $user ??= auth()->user();
        $rules = $this->rulesFor($subject->getMorphClass(), $triggerType, $action, $transition);

        $hardFailures = [];
        $softFailures = [];
        $overridable = true;

        foreach ($rules as $rule) {
            $failures = $this->failuresFor($rule, $subject);

            $this->record($rule, $subject, $user, $action, $failures);

            if ($failures === []) {
                continue;
            }

            if ($rule->severity === 'soft') {
                $softFailures = array_merge($softFailures, $failures);
                $this->raiseTask($rule, $subject);

                continue;
            }

            $hardFailures = array_merge($hardFailures, $failures);
            $overridable = $overridable && $rule->is_overridable;
        }

        if ($hardFailures !== []) {
            return GateResult::block($hardFailures, $overridable);
        }

        return $softFailures === [] ? GateResult::pass() : GateResult::warn($softFailures);
    }

    /**
     * @return list<GateFailure>
     */
    private function failuresFor(GateRule $rule, Model $subject): array
    {
        $failures = [];

        foreach ($rule->conditions ?? [] as $condition) {
            $key = (string) ($condition['check'] ?? '');
            $params = (array) ($condition['params'] ?? []);
            $check = $this->checks->find($key);

            // An unknown check is a missing implementation, not a pass: a rule
            // that silently does nothing is worse than one that fails loudly.
            if ($check === null) {
                $failures[] = new GateFailure(
                    $rule->key,
                    $key,
                    "This rule refers to a check that is not implemented: {$key}.",
                    $rule->severity,
                );

                continue;
            }

            if ($check->passes($subject, $params)) {
                continue;
            }

            $resolution = $check->resolution($subject, $params);

            $failures[] = new GateFailure(
                rule: $rule->key,
                condition: $key,
                // The check speaks first: it can name the actual shortfall,
                // where the rule's stored text can only describe the rule.
                message: $check->failureMessage($subject, $params)
                    ?: (string) ($condition['message_en'] ?? $rule->block_message_en),
                severity: $rule->severity,
                resolutionLabel: $resolution['label'] ?? $rule->resolution_label,
                resolutionUrl: $resolution['url'] ?? null,
            );
        }

        return $failures;
    }

    /**
     * @param  array{field: string, to: string, from: string}|null  $transition
     * @return Collection<int, GateRule>
     */
    private function rulesFor(string $subjectType, string $triggerType, ?string $action, ?array $transition): Collection
    {
        /** @var Collection<int, GateRule> $rules */
        $rules = Cache::remember(
            "gates:{$subjectType}:{$triggerType}",
            (int) config('walidia.gates.cache_seconds', 300),
            fn () => GateRule::query()
                ->where('subject_type', $subjectType)
                ->where('trigger_type', $triggerType)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        );

        return $rules->filter(function (GateRule $rule) use ($action, $transition): bool {
            if ($action !== null) {
                return $rule->action_key === $action;
            }

            if ($transition === null) {
                return true;
            }

            if ($rule->trigger_field !== $transition['field'] || $rule->trigger_to !== $transition['to']) {
                return false;
            }

            /** @var list<string> $from */
            $from = $rule->trigger_from ?? ['*'];

            return in_array('*', $from, true) || in_array($transition['from'], $from, true);
        })->values();
    }

    /**
     * @param  list<GateFailure>  $failures
     */
    private function record(GateRule $rule, Model $subject, ?User $user, ?string $action, array $failures): void
    {
        GateEvaluation::create([
            'gate_rule_id' => $rule->id,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'user_id' => $user?->id,
            'action_key' => $action ?? $rule->action_key,
            'result' => match (true) {
                $failures === [] => 'pass',
                $rule->severity === 'soft' => 'warn',
                default => 'block',
            },
            'failed_conditions' => $failures === []
                ? null
                : array_map(fn (GateFailure $failure): array => $failure->toArray(), $failures),
            'evaluated_at' => now(),
        ]);
    }

    /** Soft gates ask someone to look; that ask is a task, not a toast. */
    private function raiseTask(GateRule $rule, Model $subject): void
    {
        $definition = $rule->creates_task;

        if (! is_array($definition) || $definition === []) {
            return;
        }

        $exists = Task::where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('source_key', $rule->key)
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'title' => (string) ($definition['title'] ?? $rule->name_en),
            'type' => 'ops',
            'priority' => 'high',
            'assigned_role' => $definition['role'] ?? null,
            'due_at' => now()->addHours((int) ($definition['due_offset_hours'] ?? 24)),
            'status' => 'open',
            'source' => 'gate',
            'source_key' => $rule->key,
        ]);
    }
}
