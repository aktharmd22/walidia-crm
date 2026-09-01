<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\OwnerStatementResource;
use App\Models\ManagementAgreement;
use App\Models\OwnerStatement;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What the owner earned this period, and what it cost.
 *
 * Issuing is separate from drafting, because a statement sent by accident is
 * a conversation with a royal client nobody wants to have.
 *
 * @extends ResourceController<OwnerStatement>
 */
class OwnerStatementController extends ResourceController
{
    protected string $model = OwnerStatement::class;

    protected string $name = 'owner-statements';

    protected string $pages = 'Management/OwnerStatements';

    protected string $resource = OwnerStatementResource::class;

    protected ?string $routePrefix = 'management.owner-statements';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht', 'agreement.owner.client'];

    protected array $sortable = ['reference', 'period_start', 'net_to_owner', 'status'];

    protected string $defaultSort = '-period_start';

    protected array $filterable = ['status', 'yacht_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', OwnerStatement::class);

        $record = OwnerStatement::create($this->validated($request));

        return redirect()->route('management.owner-statements.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, OwnerStatement $ownerStatement): RedirectResponse
    {
        $this->authorize('update', $ownerStatement);

        $ownerStatement->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    public function issue(Request $request, OwnerStatement $ownerStatement): RedirectResponse
    {
        $this->authorize('issue', $ownerStatement);

        $ownerStatement->recalculate();
        $ownerStatement->forceFill(['status' => 'issued', 'issued_at' => now()])->save();
        $ownerStatement->logActivity('status_change', 'Statement issued to the owner');

        return back()->with('success', 'Statement issued.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'agreements' => ManagementAgreement::with('yacht:id,name')->where('status', 'active')->get()
                ->map(fn (ManagementAgreement $agreement): array => [
                    'value' => $agreement->id,
                    'label' => sprintf('%s · %s', $agreement->reference, $agreement->yacht?->name ?? 'Yacht'),
                ])
                ->all(),
            'yachts' => Yacht::orderBy('name')->get(['id', 'name'])
                ->map(fn (Yacht $yacht): array => ['value' => $yacht->id, 'label' => (string) $yacht->name])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'management_agreement_id' => ['required', 'integer', 'exists:management_agreements,id'],
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after:period_start'],
            'charter_revenue' => ['required', 'numeric', 'min:0'],
            'management_fee' => ['required', 'numeric', 'min:0'],
            'operating_costs' => ['required', 'numeric', 'min:0'],
            'maintenance_costs' => ['required', 'numeric', 'min:0'],
            'crew_costs' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'issued', 'approved', 'paid'])],
        ]);
    }
}
