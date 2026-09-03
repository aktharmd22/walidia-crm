<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\MaintenanceScheduleResource;
use App\Models\MaintenanceJob;
use App\Models\MaintenanceSchedule;
use App\Models\Vendor;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Preventive maintenance that recurs.
 *
 * Whichever comes first — the calendar or the engine hours — decides when the
 * work is next due.
 *
 * @extends ResourceController<MaintenanceSchedule>
 */
class MaintenanceScheduleController extends ResourceController
{
    protected string $model = MaintenanceSchedule::class;

    protected string $name = 'maintenance-schedules';

    protected string $pages = 'Management/MaintenanceSchedules';

    protected string $resource = MaintenanceScheduleResource::class;

    protected ?string $routePrefix = 'management.maintenance-schedules';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht', 'vendor'];

    protected array $sortable = ['next_due_on', 'system', 'title'];

    protected string $defaultSort = 'next_due_on';

    protected array $filterable = ['system', 'yacht_id', 'is_active'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceSchedule::class);

        $record = MaintenanceSchedule::create($this->validated($request));

        return redirect()->route('management.maintenance-schedules.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        $this->authorize('update', $maintenanceSchedule);

        $maintenanceSchedule->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /** Raise the job this schedule is asking for, and roll the date forward. */
    public function raiseJob(Request $request, MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        $this->authorize('update', $maintenanceSchedule);

        $job = MaintenanceJob::create([
            'yacht_id' => $maintenanceSchedule->yacht_id,
            'vendor_id' => $maintenanceSchedule->vendor_id,
            'category' => 'routine',
            'title' => $maintenanceSchedule->title,
            'description' => $maintenanceSchedule->description,
            'priority' => $maintenanceSchedule->blocks_charter ? 'high' : 'normal',
            'due_on' => $maintenanceSchedule->next_due_on,
            'estimated_cost' => $maintenanceSchedule->budget_cost,
            'currency' => 'AED',
            'blocks_charter' => $maintenanceSchedule->blocks_charter,
            'status' => 'open',
        ]);

        $maintenanceSchedule->markDone(now());

        return redirect()->route('management.maintenance.show', $job)
            ->with('success', 'Job raised and the schedule rolled forward.');
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
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'system' => ['required', Rule::in(['engines', 'generator', 'air_conditioning', 'electrical', 'plumbing', 'hull', 'cleaning'])],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'interval_engine_hours' => ['nullable', 'integer', 'min:1'],
            'last_done_on' => ['nullable', 'date'],
            'next_due_on' => ['nullable', 'date'],
            'budget_cost' => ['nullable', 'numeric', 'min:0'],
            'blocks_charter' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}
