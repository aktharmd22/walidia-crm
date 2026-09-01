<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\DealResource;
use App\Models\Deal;
use App\Models\LostReason;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The pipeline board.
 *
 * Dragging a card is a stage transition, which is exactly what the gate engine
 * guards. In this phase the move is authorised and audited; from Phase 3 the
 * evaluator runs here and a blocked move returns 422 with the reason.
 *
 * @extends ResourceController<Deal>
 */
class DealController extends ResourceController
{
    protected string $model = Deal::class;

    protected string $name = 'deals';

    protected string $pages = 'Deals';

    protected string $resource = DealResource::class;

    protected array $indexWith = ['stage:id,name,key,colour_token,probability', 'pipeline:id,key,name', 'client:id,full_name', 'assignee:id,name'];

    protected array $showWith = ['stage', 'pipeline', 'client', 'company', 'assignee', 'yacht', 'lostReason'];

    protected array $sortable = ['title', 'value', 'expected_close_date', 'created_at', 'stage_entered_at'];

    protected array $filterable = ['pipeline_id', 'stage_id', 'status', 'assigned_user_id'];

    /** The board: columns are stages, cards are deals. */
    public function board(Request $request): Response
    {
        $this->authorize('viewAny', Deal::class);

        $pipeline = Pipeline::with('stages')
            ->where('key', $request->query('pipeline', 'charter'))
            ->firstOrFail();

        $deals = Deal::query()
            ->open()
            ->where('pipeline_id', $pipeline->id)
            ->with($this->indexWith)
            ->when($request->boolean('mine'), fn (Builder $query) => $query->where('assigned_user_id', $request->user()->id))
            ->orderByDesc('value')
            ->get()
            ->groupBy('stage_id');

        return Inertia::render('Deals/Board', [
            'pipeline' => [
                'id' => $pipeline->id,
                'key' => $pipeline->key,
                'name' => $pipeline->name,
            ],
            'pipelines' => Pipeline::where('is_active', true)->get(['id', 'key', 'name']),
            'columns' => $pipeline->stages->map(fn (PipelineStage $stage): array => [
                'id' => $stage->id,
                'key' => $stage->key,
                'name' => $stage->name,
                'tone' => $stage->colour_token,
                'probability' => $stage->probability,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'cards' => DealResource::collection($deals->get($stage->id) ?? collect())->resolve(),
                'total' => (float) ($deals->get($stage->id)?->sum('value') ?? 0),
            ]),
            'filters' => ['mine' => $request->boolean('mine')],
            'lostReasons' => LostReason::where('is_active', true)
                ->where(fn (Builder $query) => $query->whereNull('pipeline_id')->orWhere('pipeline_id', $pipeline->id))
                ->get(['id', 'label']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Deal::class);

        $deal = Deal::create($this->validated($request) + ['status' => 'open']);
        $deal->logActivity('system', 'Deal opened');

        return redirect()->route('deals.show', $deal)->with('success', "Deal {$deal->reference} opened.");
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $this->authorize('update', $deal);

        $deal->update($this->validated($request, $deal));

        return back()->with('success', 'Deal updated.');
    }

    /**
     * Moving a card. Every move is recorded on the timeline with where it came
     * from — "who moved this to Negotiation, and when" is a question managers
     * ask constantly.
     */
    public function moveStage(Request $request, Deal $deal): RedirectResponse
    {
        $this->authorize('moveStage', $deal);

        $data = $request->validate([
            'stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'lost_reason_id' => ['nullable', 'integer', 'exists:lost_reasons,id'],
            'lost_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $stage = PipelineStage::findOrFail($data['stage_id']);

        abort_unless($stage->pipeline_id === $deal->pipeline_id, 422, 'That stage belongs to a different pipeline.');

        if ($stage->is_lost && empty($data['lost_reason_id'])) {
            return back()->withErrors(['lost_reason_id' => 'Choose why this was lost — a lost deal without a reason cannot be reported on.']);
        }

        $from = $deal->stage->name;

        $deal->forceFill([
            'stage_id' => $stage->id,
            'stage_entered_at' => now(),
            'status' => match (true) {
                $stage->is_won => 'won',
                $stage->is_lost => 'lost',
                default => 'open',
            },
            'closed_at' => ($stage->is_won || $stage->is_lost) ? now() : null,
            'lost_reason_id' => $stage->is_lost ? $data['lost_reason_id'] : null,
            'lost_notes' => $stage->is_lost ? ($data['lost_notes'] ?? null) : null,
        ])->save();

        $deal->logActivity('status_change', "Moved from {$from} to {$stage->name}");

        return back()->with('success', "Moved to {$stage->name}.");
    }

    protected function indexProps(Request $request): array
    {
        return [
            'pipelines' => Pipeline::where('is_active', true)->with('stages:id,pipeline_id,name,key')->get(),
        ];
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'pipelines' => Pipeline::where('is_active', true)->with('stages:id,pipeline_id,name,key,sort_order')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Deal $deal = null): array
    {
        return $request->validate([
            'pipeline_id' => ['required', 'integer', 'exists:pipelines,id'],
            'stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'title' => ['required', 'string', 'max:190'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'expected_close_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }
}
