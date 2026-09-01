<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\ManagementAgreementResource;
use App\Models\ManagementAgreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The management mandate: what we run, for whom, and on what fee.
 *
 * @extends ResourceController<ManagementAgreement>
 */
class ManagementAgreementController extends ResourceController
{
    protected string $model = ManagementAgreement::class;

    protected string $name = 'management-agreements';

    protected string $pages = 'Management/Agreements';

    protected string $resource = ManagementAgreementResource::class;

    protected ?string $routePrefix = 'management.agreements';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht', 'owner.client', 'statements', 'maintenanceJobs'];

    protected array $sortable = ['reference', 'starts_on', 'ends_on', 'status'];

    protected string $defaultSort = '-starts_on';

    protected array $filterable = ['status', 'scope', 'yacht_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ManagementAgreement::class);

        $record = ManagementAgreement::create($this->validated($request));

        return redirect()->route('management.agreements.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, ManagementAgreement $agreement): RedirectResponse
    {
        $this->authorize('update', $agreement);

        $agreement->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'yacht_owner_id' => ['nullable', 'integer', 'exists:yacht_owners,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'scope' => ['required', Rule::in(['full', 'technical', 'crew_only', 'charter_only'])],
            'fee_model' => ['required', Rule::in(['fixed', 'percentage', 'hybrid'])],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after:starts_on'],
            'notice_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'opex_budget_annual' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'active', 'expiring', 'ended'])],
        ]);
    }
}
