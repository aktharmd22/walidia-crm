<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Domain\Automation\WorkflowEngine;
use App\Domain\Gates\GateEvaluator;
use App\Http\Controllers\Concerns\PicksOperationsContext;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\SecurityDepositResource;
use App\Models\DamageReport;
use App\Models\SecurityDeposit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The client's money, held. Releasing it is gated on a closed damage
 * inspection, because that is the moment when releasing early is both most
 * tempting and most expensive.
 *
 * @extends ResourceController<SecurityDeposit>
 */
class SecurityDepositController extends ResourceController
{
    use PicksOperationsContext;

    protected string $model = SecurityDeposit::class;

    protected string $name = 'security-deposits';

    protected string $pages = 'Finance/SecurityDeposits';

    protected string $resource = SecurityDepositResource::class;

    protected ?string $routePrefix = 'finance.security-deposits';

    protected array $indexWith = ['booking.client:id,full_name'];

    protected array $showWith = ['booking.client', 'booking.damageReports'];

    protected array $sortable = ['amount', 'status', 'collected_at'];

    protected string $defaultSort = '-collected_at';

    protected array $filterable = ['status', 'method'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SecurityDeposit::class);

        $deposit = SecurityDeposit::create($this->validated($request) + [
            'status' => 'held',
            'collected_at' => now(),
            'collected_by' => $request->user()->id,
        ]);

        return redirect()->route('finance.security-deposits.show', $deposit)
            ->with('success', 'Security deposit recorded as held.');
    }

    public function update(Request $request, SecurityDeposit $securityDeposit): RedirectResponse
    {
        $this->authorize('update', $securityDeposit);

        $securityDeposit->update($this->validated($request));

        return back()->with('success', 'Security deposit updated.');
    }

    public function collect(Request $request, SecurityDeposit $securityDeposit): RedirectResponse
    {
        $this->authorize('collect', $securityDeposit);

        $securityDeposit->forceFill([
            'status' => 'held',
            'collected_at' => now(),
            'collected_by' => $request->user()->id,
        ])->save();

        return back()->with('success', 'Deposit collected.');
    }

    /**
     * A partial release is the normal case after damage: the deduction reason
     * is recorded on the deposit so the client statement can quote it.
     */
    public function release(Request $request, SecurityDeposit $securityDeposit, GateEvaluator $gates): RedirectResponse
    {
        $this->authorize('release', $securityDeposit);

        $data = $request->validate([
            'released_amount' => ['nullable', 'numeric', 'min:0'],
            'deduction_reason' => ['nullable', 'string', 'max:190'],
            'override_reason' => ['nullable', 'string'],
        ]);

        $reason = ($data['override_reason'] ?? null) !== null && $request->user()->can('gates.override')
            ? (string) $data['override_reason']
            : null;

        $reason !== null
            ? $gates->override($securityDeposit, 'security-deposits.release', $request->user(), $reason)
            : $gates->assertAction($securityDeposit, 'security-deposits.release', $request->user());

        $released = (float) ($data['released_amount'] ?? $securityDeposit->amount);

        $securityDeposit->forceFill([
            'status' => $released >= (float) $securityDeposit->amount ? 'released' : 'partially_released',
            'released_amount' => $released,
            'released_at' => now(),
            'released_by' => $request->user()->id,
            'deduction_reason' => $data['deduction_reason'] ?? null,
        ])->save();

        $securityDeposit->booking?->logActivity(
            'system',
            'Security deposit released',
            $data['deduction_reason'] ?? null,
        );

        app(WorkflowEngine::class)->fire('deposit.released', $securityDeposit);

        return back()->with('success', 'Security deposit released.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return ['bookings' => $this->bookingOptions()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var SecurityDeposit $record */
        return [
            'gate' => app(GateEvaluator::class)
                ->forAction($record, 'security-deposits.release', $request->user())
                ->toArray(),
            // Naming the open inspections turns "blocked" into "blocked by this".
            'openDamage' => $record->booking?->damageReports
                ->where('inspection_status', '!=', 'closed')
                ->map(fn (DamageReport $report): array => [
                    'id' => $report->id,
                    'reference' => $report->reference,
                    'description' => $report->description,
                    'estimated_cost' => $report->estimated_cost,
                ])->values() ?? [],
            'can' => $this->recordAbilities($request, $record) + [
                'release' => $request->user()->can('release', $record),
                'override' => $request->user()->can('gates.override'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'method' => ['required', Rule::in(['card_hold', 'cash', 'transfer'])],
        ]);
    }
}
