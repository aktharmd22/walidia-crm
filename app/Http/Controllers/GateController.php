<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Gates\GateCheckRegistry;
use App\Domain\Gates\GateEvaluator;
use App\Models\GateOverride;
use App\Models\GateRule;
use App\Support\Paginate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The gate engine's own endpoints.
 *
 * `evaluate` is the dry run every screen calls to answer "why is this button
 * disabled" with the same reasoning the write path uses. `overrides` is the
 * register: read-only for everybody, including Admin.
 */
class GateController extends Controller
{
    public function evaluate(Request $request, GateEvaluator $gates): JsonResponse
    {
        $data = $request->validate([
            'subject_type' => ['required', 'string', Rule::in(array_keys(Relation::morphMap()))],
            'subject_id' => ['required', 'integer'],
            'action' => ['required', 'string', 'max:96'],
        ]);

        $class = Relation::getMorphedModel($data['subject_type']);
        abort_if($class === null, 404);

        /** @var Model $subject */
        $subject = $class::findOrFail($data['subject_id']);

        // Evaluating is a read of the record, so it needs the same permission.
        $this->authorize('view', $subject);

        return response()->json(
            $gates->forAction($subject, $data['action'], $request->user())->toArray(),
        );
    }

    /**
     * The Override Register. Every hard gate someone chose to walk past, with
     * the reason they gave, permanently.
     */
    public function overrides(Request $request): Response
    {
        $this->authorize('viewAny', GateOverride::class);

        return Inertia::render('Compliance/Overrides', [
            'rows' => Paginate::shape(GateOverride::query()
                ->with(['rule:id,key,name_en,severity', 'user:id,name'])
                ->latest('created_at')
                ->paginate(50)
                ->through(fn (GateOverride $override): array => [
                    'id' => $override->id,
                    'rule' => $override->rule?->name_en ?? 'Rule removed',
                    'rule_key' => $override->rule?->key,
                    'subject_type' => $override->subject_type,
                    'subject_id' => $override->subject_id,
                    'user' => $override->user?->name ?? 'Unknown',
                    'reason' => $override->reason,
                    'failed_conditions' => $override->failed_conditions,
                    'ip_address' => $override->ip_address,
                    'created_at' => $override->created_at?->toIso8601String(),
                ])),
        ]);
    }

    /** The rule editor: rules are data, and Admin owns them. */
    public function rules(Request $request): Response
    {
        $this->authorize('viewAny', GateRule::class);

        return Inertia::render('Automation/GateRules', [
            'rules' => GateRule::query()
                ->orderBy('subject_type')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (GateRule $rule): array => [
                    'id' => $rule->id,
                    'key' => $rule->key,
                    'name' => $rule->name_en,
                    'subject_type' => $rule->subject_type,
                    'trigger' => $rule->trigger_type === 'action'
                        ? $rule->action_key
                        : "{$rule->trigger_field} → {$rule->trigger_to}",
                    'severity' => $rule->severity,
                    'conditions' => $rule->conditions,
                    'block_message' => $rule->block_message_en,
                    'is_active' => $rule->is_active,
                    'is_overridable' => $rule->is_overridable,
                    'version' => $rule->version,
                ]),
            'checks' => app(GateCheckRegistry::class)->keys(),
        ]);
    }

    public function toggleRule(Request $request, GateRule $rule): RedirectResponse
    {
        $this->authorize('update', $rule);

        $rule->forceFill(['is_active' => ! $rule->is_active])->save();

        return back()->with(
            'success',
            $rule->is_active ? "Rule {$rule->key} is active." : "Rule {$rule->key} is switched off.",
        );
    }
}
