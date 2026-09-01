<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Concerns\PicksOperationsContext;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\DamageReportResource;
use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Damage found at handover. An open inspection holds the security deposit —
 * that is the whole point of recording it here rather than in a WhatsApp thread.
 *
 * @extends ResourceController<DamageReport>
 */
class DamageReportController extends ResourceController
{
    use PicksOperationsContext;

    protected string $model = DamageReport::class;

    protected string $name = 'damage-reports';

    protected string $pages = 'Charter/DamageReports';

    protected string $resource = DamageReportResource::class;

    protected ?string $routePrefix = 'charter.damage-reports';

    protected array $indexWith = ['booking:id,reference', 'yacht:id,name'];

    protected array $showWith = ['booking.client', 'booking.securityDeposit', 'yacht'];

    protected array $sortable = ['reference', 'discovered_at', 'inspection_status'];

    protected string $defaultSort = '-discovered_at';

    protected array $filterable = ['inspection_status', 'booking_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DamageReport::class);

        $report = DamageReport::create($this->validated($request) + [
            'discovered_by' => $request->user()->id,
            'inspection_status' => 'pending',
        ]);

        return redirect()->route('charter.damage-reports.show', $report)
            ->with('warning', 'Damage recorded. The security deposit stays held until this inspection is closed.');
    }

    public function update(Request $request, DamageReport $damageReport): RedirectResponse
    {
        $this->authorize('update', $damageReport);

        $damageReport->update($this->validated($request));

        return back()->with('success', 'Damage report updated.');
    }

    /**
     * Closing the inspection is what unlocks the deposit release — so it asks
     * for the outcome rather than being a single click.
     */
    public function close(Request $request, DamageReport $damageReport): RedirectResponse
    {
        $this->authorize('close', $damageReport);

        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:2000'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'deduct_from_deposit' => ['boolean'],
        ]);

        $damageReport->forceFill($data + [
            'inspection_status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $request->user()->id,
        ])->save();

        $damageReport->booking?->logActivity(
            'system',
            "Damage inspection closed: {$damageReport->reference}",
            $data['resolution'],
        );

        return back()->with('success', 'Inspection closed. The security deposit can now be released.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'bookings' => $this->bookingOptions(),
            'yachts' => $this->yachtOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'yacht_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'discovered_at' => ['required', 'date'],
            'description' => ['required', 'string', 'max:5000'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'deduct_from_deposit' => ['boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        return [
            'can' => $this->recordAbilities($request, $record) + [
                'close' => $request->user()->can('close', $record),
            ],
        ];
    }
}
