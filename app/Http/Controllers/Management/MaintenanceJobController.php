<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\MaintenanceJobResource;
use App\Models\MaintenanceJob;
use App\Models\Vendor;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Work on a managed yacht. A job that blocks charter says so, on the job and on the calendar.
 *
 * @extends ResourceController<MaintenanceJob>
 */
class MaintenanceJobController extends ResourceController
{
    protected string $model = MaintenanceJob::class;

    protected string $name = 'maintenance';

    protected string $pages = 'Management/Maintenance';

    protected string $resource = MaintenanceJobResource::class;

    protected ?string $routePrefix = 'management.maintenance';

    protected array $indexWith = ['yacht:id,name', 'vendor:id,legal_name,trade_name'];

    protected array $showWith = ['yacht', 'vendor', 'agreement'];

    protected array $sortable = ['reference', 'due_on', 'priority', 'status'];

    protected string $defaultSort = 'due_on';

    protected array $filterable = ['status', 'category', 'priority', 'yacht_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceJob::class);

        $record = MaintenanceJob::create($this->validated($request));

        return redirect()->route('management.maintenance.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, MaintenanceJob $maintenance): RedirectResponse
    {
        $this->authorize('update', $maintenance);

        $maintenance->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /** Done means done: what it cost, and when. */
    public function complete(Request $request, MaintenanceJob $maintenance): RedirectResponse
    {
        $this->authorize('complete', $maintenance);

        $data = $request->validate([
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $maintenance->forceFill([
            'actual_cost' => $data['actual_cost'] ?? $maintenance->actual_cost,
            'status' => 'done',
            'completed_at' => now(),
        ])->save();

        $maintenance->logActivity('status_change', 'Maintenance completed', $data['notes'] ?? null);

        return back()->with('success', 'Job marked done.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'yachts' => Yacht::orderBy('name')->get(['id', 'name'])
                ->map(fn (Yacht $yacht): array => ['value' => $yacht->id, 'label' => (string) $yacht->name])
                ->all(),
            'vendors' => Vendor::where('is_approved', true)->orderBy('legal_name')->get()
                ->map(fn (Vendor $vendor): array => ['value' => $vendor->id, 'label' => $vendor->displayName()])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'management_agreement_id' => ['nullable', 'integer', 'exists:management_agreements,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category' => ['required', Rule::in(['routine', 'repair', 'refit', 'warranty', 'survey'])],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'critical'])],
            'due_on' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'owner_approval_required' => ['boolean'],
            'blocks_charter' => ['boolean'],
            'status' => ['required', Rule::in(['open', 'scheduled', 'in_progress', 'done', 'cancelled'])],
        ]);
    }
}
