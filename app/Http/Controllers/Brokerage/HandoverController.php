<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\HandoverResource;
use App\Models\Handover;
use App\Models\Marina;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Keys, documents, inventory, flag, insurance. A sale is not finished until all five have moved.
 *
 * @extends ResourceController<Handover>
 */
class HandoverController extends ResourceController
{
    protected string $model = Handover::class;

    protected string $name = 'handovers';

    protected string $pages = 'Brokerage/Handovers';

    protected string $resource = HandoverResource::class;

    protected ?string $routePrefix = 'brokerage.handovers';

    protected array $indexWith = ['transaction:id,reference'];

    protected array $showWith = ['transaction.listing.yacht'];

    protected array $sortable = ['reference', 'scheduled_at', 'status'];

    protected string $defaultSort = '-scheduled_at';

    protected array $filterable = ['status', 'transaction_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Handover::class);

        $record = Handover::create($this->validated($request));

        return redirect()->route('brokerage.handovers.show', $record)->with('success', 'Handover created.');
    }

    public function update(Request $request, Handover $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Handover updated.');
    }

    /** Completion is derived from the five facts, not asserted separately. */
    public function complete(Request $request, Handover $handover): RedirectResponse
    {
        $this->authorize('update', $handover);

        if (! $handover->isComplete()) {
            return back()->withErrors([
                'gate' => 'Every handover item must be ticked before the handover can be closed.',
            ]);
        }

        $handover->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $handover->logActivity('status_change', 'Handover completed');

        return back()->with('success', 'Handover completed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'transactions' => Transaction::latest('id')->limit(200)->get(['id', 'reference'])
                ->map(fn (Transaction $transaction): array => [
                    'value' => $transaction->id,
                    'label' => (string) $transaction->reference,
                ])
                ->all(),
            'marinas' => Marina::orderBy('name')->get(['id', 'name'])
                ->map(fn (Marina $marina): array => ['value' => $marina->id, 'label' => (string) $marina->name])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'scheduled_at' => ['nullable', 'date'],
            'keys_handed_over' => ['boolean'],
            'documents_handed_over' => ['boolean'],
            'inventory_signed' => ['boolean'],
            'flag_registration_updated' => ['boolean'],
            'insurance_transferred' => ['boolean'],
            'outstanding_items' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);
    }
}
