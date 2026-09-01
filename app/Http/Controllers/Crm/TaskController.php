<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Task>
 */
class TaskController extends ResourceController
{
    protected string $model = Task::class;

    protected string $name = 'tasks';

    protected string $pages = 'Tasks';

    protected string $resource = TaskResource::class;

    protected array $indexWith = ['assignee:id,name', 'subject'];

    protected array $showWith = ['assignee', 'subject'];

    protected array $sortable = ['title', 'due_at', 'priority', 'status', 'created_at'];

    protected string $defaultSort = 'due_at';

    protected array $filterable = ['status', 'type', 'priority', 'assigned_user_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $task = Task::create($this->validated($request) + [
            'assigned_user_id' => $request->input('assigned_user_id', $request->user()->id),
            'source' => 'manual',
        ]);

        return back()->with('success', "Task {$task->reference} created.");
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update($this->validated($request, $task));

        return back()->with('success', 'Task updated.');
    }

    public function complete(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);

        $task->complete($request->user());

        $subject = $task->subject;
        if ($subject !== null && method_exists($subject, 'logActivity')) {
            $subject->logActivity('system', "Task completed: {$task->title}");
        }

        return back()->with('success', 'Task completed.');
    }

    public function reopen(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->forceFill(['status' => 'open', 'completed_at' => null, 'completed_by' => null])->save();

        return back()->with('success', 'Task reopened.');
    }

    public function escalate(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate(['escalated_to' => ['required', 'integer', 'exists:users,id']]);

        $task->forceFill([
            'escalated_to' => $data['escalated_to'],
            'escalated_at' => now(),
            'priority' => 'urgent',
        ])->save();

        return back()->with('success', 'Task escalated.');
    }

    public function team(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);
        abort_unless($request->user()->can('records.view-team') || $request->user()->can('records.view-all'), 403);

        return Inertia::render('Tasks/Index', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->open()),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => 'Team Tasks',
            'users' => $this->users(),
        ]);
    }

    public function overdue(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        return Inertia::render('Tasks/Index', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->overdue()),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => 'Overdue',
            'users' => $this->users(),
        ]);
    }

    protected function baseQuery(Request $request): Builder
    {
        // My Tasks by default: a task list that opens on everyone else's work
        // is a list nobody reads.
        return Task::query()->when(
            ! $request->has('assigned_user_id') && ! $request->routeIs('tasks.team'),
            fn (Builder $query) => $query->where('assigned_user_id', $request->user()->id),
        );
    }

    protected function indexProps(Request $request): array
    {
        return ['heading' => 'My Tasks', 'users' => $this->users()];
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return ['users' => $this->users()];
    }

    /**
     * @return Collection<int, array{value: int, label: string}>
     */
    private function users()
    {
        return User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Task $task = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['next_action', 'follow_up', 'approval', 'ops', 'compliance'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
            'subject_type' => ['nullable', 'string', 'max:64'],
            'subject_id' => ['nullable', 'integer'],
            'status' => [$task === null ? 'nullable' : 'required', Rule::in(['open', 'done', 'cancelled'])],
        ]);
    }
}
