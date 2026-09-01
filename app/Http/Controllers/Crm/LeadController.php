<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use App\Services\DuplicateChecker;
use App\Services\LeadConverter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Lead>
 */
class LeadController extends ResourceController
{
    protected string $model = Lead::class;

    protected string $name = 'leads';

    protected string $pages = 'Leads';

    protected string $resource = LeadResource::class;

    protected array $indexWith = ['source:id,name', 'assignee:id,name', 'client:id,full_name'];

    protected array $showWith = ['source', 'assignee', 'client', 'company', 'duplicateOf'];

    protected array $sortable = ['name', 'reference', 'status', 'created_at', 'sla_due_at'];

    protected array $filterable = ['status', 'business_line', 'source_id', 'assigned_user_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $data = $this->validated($request);

        $lead = Lead::create($data + [
            'status' => 'new',
            // The SLA clock starts the moment the lead lands, not when someone
            // opens it — that is what makes the Follow-Up Pool honest.
            'sla_due_at' => now()->addHours(4),
        ]);

        $lead->logActivity('system', 'Lead captured', $lead->message);

        return redirect()->route('leads.show', $lead)->with('success', "Lead {$lead->reference} created.");
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update($this->validated($request, $lead));

        return back()->with('success', 'Lead updated.');
    }

    /* ── queues ─────────────────────────────────────────────────────────── */

    public function inbox(Request $request): Response
    {
        return $this->queue($request, fn (Builder $query) => $query->where('status', 'new'), 'Inbox');
    }

    public function unassigned(Request $request): Response
    {
        return $this->queue($request, fn (Builder $query) => $query->whereNull('assigned_user_id'), 'Unassigned');
    }

    public function followUp(Request $request): Response
    {
        return $this->queue(
            $request,
            fn (Builder $query) => $query->whereIn('status', ['new', 'contacted'])
                ->where(function (Builder $query): void {
                    $query->whereNull('first_response_at')->orWhereNotNull('next_follow_up_at');
                })
                ->orderByRaw('COALESCE(next_follow_up_at, sla_due_at) asc'),
            'Follow-Up Pool',
        );
    }

    public function duplicates(Request $request, DuplicateChecker $checker): Response
    {
        $this->authorize('viewAny', Lead::class);

        return Inertia::render('Leads/Duplicates', [
            'pairs' => $checker->probableLeadDuplicates()
                ->map(fn (array $pair): array => [
                    'lead' => LeadResource::make($pair['lead'])->resolve(),
                    'match' => [
                        'id' => $pair['match']->id,
                        'name' => $pair['match']->full_name,
                        'reference' => $pair['match']->reference,
                        'url' => route('clients.show', $pair['match']->id),
                    ],
                    'score' => $pair['score'],
                    'reason' => $pair['reason'],
                ])
                ->all(),
        ]);
    }

    /* ── actions ────────────────────────────────────────────────────────── */

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('assign', $lead);

        $data = $request->validate(['assigned_user_id' => ['required', 'integer', 'exists:users,id']]);

        $lead->update($data);
        $lead->logActivity('system', 'Lead assigned to '.User::find($data['assigned_user_id'])?->name);

        return back()->with('success', 'Lead assigned.');
    }

    public function logContact(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'channel' => ['required', Rule::in(['call', 'whatsapp', 'email', 'meeting'])],
            'summary' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'next_follow_up_at' => ['nullable', 'date', 'after:now'],
        ]);

        $lead->forceFill([
            'status' => $lead->status === 'new' ? 'contacted' : $lead->status,
            'first_response_at' => $lead->first_response_at ?? now(),
            'next_follow_up_at' => $data['next_follow_up_at'] ?? $lead->next_follow_up_at,
        ])->save();

        $lead->logActivity($data['channel'], $data['summary'], $data['body'] ?? null, direction: 'outbound');

        return back()->with('success', 'Contact logged.');
    }

    public function qualify(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'outcome' => ['required', Rule::in(['qualified', 'unqualified'])],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        $lead->forceFill([
            'status' => $data['outcome'],
            'unqualified_reason' => $data['outcome'] === 'unqualified' ? $data['reason'] : null,
        ])->save();

        $lead->logActivity('status_change', 'Lead marked '.$data['outcome'], $data['reason'] ?? null);

        return back()->with('success', 'Lead '.$data['outcome'].'.');
    }

    public function convert(Request $request, Lead $lead, LeadConverter $converter): RedirectResponse
    {
        $this->authorize('convert', $lead);

        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'create_deal' => ['boolean'],
        ]);

        $client = $converter->convert($lead, $data['client_id'] ?? null, (bool) ($data['create_deal'] ?? true));

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Lead converted to {$client->reference}.");
    }

    public function merge(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('merge', $lead);

        $data = $request->validate(['into_lead_id' => ['required', 'integer', 'exists:leads,id', 'different:lead']]);

        $lead->forceFill(['status' => 'duplicate', 'duplicate_of_id' => $data['into_lead_id']])->save();
        $lead->logActivity('system', 'Marked as duplicate');

        return back()->with('success', 'Lead marked as a duplicate.');
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    /**
     * @param  callable(Builder<Lead>): Builder<Lead>  $modifier
     */
    private function queue(Request $request, callable $modifier, string $heading): Response
    {
        $this->authorize('viewAny', Lead::class);

        return Inertia::render('Leads/Index', [
            'rows' => $this->paginate($request, $modifier),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => $heading,
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (LeadSource $source): array => ['value' => $source->id, 'label' => $source->name]),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]),
        ];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Lead $record */
        return [
            'timeline' => ActivityResource::collection(
                $record->activities()->with('user:id,name')->limit(25)->get(),
            )->resolve(),
            'can' => array_merge($this->recordAbilities($request, $record), [
                'convert' => $request->user()->can('convert', $record),
                'assign' => $request->user()->can('assign', $record),
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Lead $lead = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'business_line' => ['required', Rule::in(['charter', 'brokerage', 'management'])],
            'source_id' => ['nullable', 'integer', 'exists:lead_sources,id'],
            'message' => ['nullable', 'string', 'max:5000'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => [$lead === null ? 'nullable' : 'required', Rule::in(['new', 'contacted', 'qualified', 'registered', 'unqualified', 'duplicate'])],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);
    }
}
