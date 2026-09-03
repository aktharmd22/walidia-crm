<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\WorkflowRuleResource;
use App\Models\MessageTemplate;
use App\Models\WorkflowRule;
use App\Models\WorkflowRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * When the system says it.
 *
 * Rules are rows, like the gate rules — moving a reminder from 24 hours to 48
 * is an edit, not a deployment.
 *
 * @extends ResourceController<WorkflowRule>
 */
class WorkflowRuleController extends ResourceController
{
    protected string $model = WorkflowRule::class;

    protected string $name = 'workflows';

    protected string $pages = 'Automation/Workflows';

    protected string $resource = WorkflowRuleResource::class;

    protected ?string $routePrefix = 'engine.workflows';

    protected array $indexWith = ['template:id,name'];

    protected array $showWith = ['template'];

    protected array $sortable = ['name', 'trigger_type', 'sort_order'];

    protected string $defaultSort = 'sort_order';

    protected array $filterable = ['business_line', 'trigger_type', 'action', 'is_active'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkflowRule::class);

        $record = WorkflowRule::create($this->validated($request));

        return redirect()->route('engine.workflows.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, WorkflowRule $workflow): RedirectResponse
    {
        $this->authorize('update', $workflow);

        $workflow->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /**
     * The register: what this rule has done, and what it skipped.
     *
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var WorkflowRule $record */
        return [
            'runs' => $record->runs()
                ->latest('due_at')
                ->limit(50)
                ->get()
                ->map(fn (WorkflowRun $run): array => [
                    'id' => $run->id,
                    'subject_type' => $run->subject_type,
                    'subject_id' => $run->subject_id,
                    'due_at' => $run->due_at->toIso8601String(),
                    'ran_at' => $run->ran_at?->toIso8601String(),
                    'status' => $run->status,
                    'skip_reason' => $run->skip_reason,
                    'error' => $run->error,
                ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'templates' => MessageTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (MessageTemplate $template): array => [
                    'value' => $template->id,
                    'label' => (string) $template->name,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:64', Rule::unique('workflow_rules', 'key')->ignore($request->route('workflow'))],
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'business_line' => ['required', Rule::in(['charter', 'brokerage', 'management'])],
            'trigger_type' => ['required', Rule::in(['event', 'schedule'])],
            'trigger_event' => ['required_if:trigger_type,event', 'nullable', 'string', 'max:64'],
            'anchor_field' => ['required_if:trigger_type,schedule', 'nullable', 'string', 'max:64'],
            'offset_hours' => ['required', 'integer', 'min:-8760', 'max:8760'],
            'action' => ['required', Rule::in(['send_message', 'create_task', 'notify_role', 'update_field'])],
            'message_template_id' => ['required_if:action,send_message', 'nullable', 'integer', 'exists:message_templates,id'],
            'audience' => ['required', Rule::in(['client', 'owner', 'crew', 'vendor', 'role'])],
            'is_active' => ['boolean'],
        ]);
    }
}
