<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\ClientRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\TaskResource;
use App\Models\Client;
use App\Models\Company;
use App\Models\LeadSource;
use App\Models\User;
use App\Services\DuplicateChecker;
use App\Support\Paginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Client>
 */
class ClientController extends ResourceController
{
    protected string $model = Client::class;

    protected string $name = 'clients';

    protected string $pages = 'Clients';

    protected string $resource = ClientResource::class;

    protected array $indexWith = ['company:id,legal_name,trade_name', 'assignee:id,name'];

    protected array $showWith = ['company', 'assignee', 'contacts', 'deals.stage', 'deals.pipeline'];

    protected array $sortable = ['full_name', 'reference', 'created_at', 'updated_at', 'vip_level', 'status'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'kyc_status', 'vip_level', 'type', 'company_id', 'assigned_user_id'];

    public function store(ClientRequest $request, DuplicateChecker $duplicates): RedirectResponse
    {
        $data = $request->validated();

        // A duplicate client record is how a VIP ends up with two histories.
        $match = $duplicates->findClient($data);

        if ($match !== null && ! $request->boolean('force')) {
            return back()
                ->withInput()
                ->with('warning', "This looks like an existing client: {$match->full_name} ({$match->reference}).")
                ->withErrors(['duplicate' => (string) $match->id]);
        }

        $client = DB::transaction(function () use ($data, $request): Client {
            $client = Client::create($data + [
                'assigned_user_id' => $data['assigned_user_id'] ?? $request->user()->id,
            ]);

            $client->logActivity('system', 'Client record created');

            return $client;
        });

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "{$client->full_name} added as {$client->reference}.");
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return back()->with('success', 'Client updated.');
    }

    /* ── scoped list screens ────────────────────────────────────────────── */

    public function vip(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);
        abort_unless($request->user()->can('clients.view-vip'), 403);

        return Inertia::render('Clients/Index', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->whereIn('vip_level', ['vip', 'uhnw', 'protected'])),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'scope' => 'vip',
            'heading' => 'VIP Register',
        ]);
    }

    public function ofType(Request $request, string $type): Response
    {
        $this->authorize('viewAny', Client::class);

        $labels = [
            'buyer' => 'Buyers',
            'seller' => 'Sellers',
            'owner' => 'Owners',
            'charter_guest' => 'Charter guests',
            'partner' => 'Partners',
        ];

        abort_unless(isset($labels[$type]), 404);

        return Inertia::render('Clients/Index', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->ofType($type)),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'scope' => $type,
            'heading' => $labels[$type],
        ]);
    }

    public function approvalQueue(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        return Inertia::render('Clients/ApprovalQueue', [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->where('status', 'pending_approval')),
            'filters' => $this->currentFilters($request),
            'can' => ['approve' => $request->user()->can('compliance.verify-kyc')],
        ]);
    }

    /* ── actions ────────────────────────────────────────────────────────── */

    public function approve(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('approve', $client);

        $client->forceFill([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        $client->logActivity('system', 'Client approved for transacting');

        return back()->with('success', "{$client->full_name} approved.");
    }

    public function verifyKyc(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('verifyKyc', $client);

        $data = $request->validate([
            'outcome' => ['required', 'in:verified,rejected'],
            'expires_on' => ['nullable', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $client->forceFill([
            'kyc_status' => $data['outcome'],
            'kyc_verified_at' => $data['outcome'] === 'verified' ? now() : null,
            'kyc_verified_by' => $request->user()->id,
            'kyc_expires_on' => $data['expires_on'] ?? null,
        ])->save();

        // The contract-generation gate reads exactly this field.
        $client->logActivity('system', 'KYC '.$data['outcome'], $data['note'] ?? null);

        return back()->with('success', 'KYC '.$data['outcome'].'.');
    }

    public function timeline(Request $request, Client $client): Response
    {
        $this->authorize('view', $client);

        return Inertia::render('Clients/Timeline', [
            'record' => ClientResource::make($client)->resolve(),
            'activities' => Paginate::shape($client->activities()->with('user:id,name')->paginate(50)),
        ]);
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    /**
     * @param  Builder<Client>  $query
     */
    protected function filterType(Builder $query, string $value): void
    {
        $query->ofType($value);
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'companies' => Company::query()
                ->orderBy('legal_name')
                ->get(['id', 'legal_name', 'trade_name'])
                ->map(fn (Company $company): array => ['value' => $company->id, 'label' => $company->displayName()]),
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (LeadSource $source): array => ['value' => $source->id, 'label' => $source->name]),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]),
            'canEditVip' => $request->user()->can('clients.view-vip'),
        ];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Client $record */
        return [
            'timeline' => ActivityResource::collection(
                $record->activities()->with('user:id,name')->limit(25)->get(),
            )->resolve(),
            'tasks' => TaskResource::collection($record->openTasks())->resolve(),
            'documents' => DocumentResource::collection(
                $record->documents()->latest()->limit(10)->get(),
            )->resolve(),
            'canViewVip' => $request->user()->can('viewVipFields', $record),
        ];
    }
}
