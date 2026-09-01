<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\ClientResource;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Company>
 */
class CompanyController extends ResourceController
{
    protected string $model = Company::class;

    protected string $name = 'companies';

    protected string $pages = 'Companies';

    protected string $resource = CompanyResource::class;

    protected array $showWith = ['contacts', 'assignee'];

    protected array $sortable = ['legal_name', 'reference', 'type', 'status', 'created_at'];

    protected string $defaultSort = 'legal_name';

    protected array $filterable = ['type', 'status', 'country'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $company = Company::create($this->validated($request));
        $company->logActivity('system', 'Company record created');

        return redirect()->route('companies.show', $company)
            ->with('success', "{$company->displayName()} added.");
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->update($this->validated($request, $company));

        return back()->with('success', 'Company updated.');
    }

    /** Partners & DMCs — the referral side of the book. */
    public function partners(Request $request): Response
    {
        $this->authorize('viewAny', Company::class);

        return Inertia::render('Companies/Index', [
            'rows' => $this->paginate(
                $request,
                fn (Builder $query) => $query->whereIn('type', ['dmc', 'concierge', 'charter_partner', 'broker']),
            ),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => 'Partners & DMCs',
        ]);
    }

    protected function baseQuery(Request $request): Builder
    {
        return Company::query()->withCount('clients');
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]),
            'types' => [
                ['value' => 'corporate', 'label' => 'Corporate client'],
                ['value' => 'dmc', 'label' => 'DMC'],
                ['value' => 'concierge', 'label' => 'Concierge'],
                ['value' => 'charter_partner', 'label' => 'Charter partner'],
                ['value' => 'broker', 'label' => 'Broker'],
                ['value' => 'supplier', 'label' => 'Supplier'],
            ],
        ];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Company $record */
        return [
            'clients' => ClientResource::collection($record->clients()->limit(50)->get())->resolve(),
            'contacts' => $record->contacts->map(fn ($contact): array => [
                'id' => $contact->id,
                'name' => $contact->name,
                'position' => $contact->position,
                'email' => $contact->email,
                'mobile' => $contact->mobile,
                'is_primary' => $contact->is_primary,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'legal_name' => ['required', 'string', 'max:190'],
            'trade_name' => ['nullable', 'string', 'max:190'],
            'type' => ['required', Rule::in(['corporate', 'dmc', 'concierge', 'charter_partner', 'broker', 'supplier'])],
            'trn' => ['nullable', 'string', 'max:32'],
            'trade_licence_no' => ['nullable', 'string', 'max:64'],
            'licence_expiry' => ['nullable', 'date'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:190'],
            'address_line1' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:90'],
            'emirate' => ['nullable', 'string', 'max:90'],
            'country' => ['nullable', 'string', 'max:90'],
            'billing_email' => ['nullable', 'email:rfc', 'max:190'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'commission_rate_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'blacklisted'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
