<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\InspectionResource;
use App\Models\Inspection;
use App\Models\Listing;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A yacht looked over before it is listed, or before it is delivered.
 *
 * @extends ResourceController<Inspection>
 */
class InspectionController extends ResourceController
{
    protected string $model = Inspection::class;

    protected string $name = 'inspections';

    protected string $pages = 'Brokerage/Inspections';

    protected string $resource = InspectionResource::class;

    protected ?string $routePrefix = 'brokerage.inspections';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht', 'listing', 'handover', 'inspector'];

    protected array $sortable = ['reference', 'inspected_at', 'type'];

    protected string $defaultSort = '-inspected_at';

    protected array $filterable = ['type', 'outcome', 'status', 'yacht_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Inspection::class);

        $record = Inspection::create($this->validated($request));

        return redirect()->route('brokerage.inspections.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, Inspection $inspection): RedirectResponse
    {
        $this->authorize('update', $inspection);

        $inspection->update($this->validated($request));

        return back()->with('success', 'Updated.');
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
            'listings' => Listing::with('yacht:id,name')->limit(300)->get()
                ->map(fn (Listing $listing): array => [
                    'value' => $listing->id,
                    'label' => sprintf('%s · %s', $listing->reference, $listing->yacht?->name ?? 'Yacht'),
                ])
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
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'handover_id' => ['nullable', 'integer', 'exists:handovers,id'],
            'type' => ['required', Rule::in(['listing', 'pre_delivery'])],
            'inspected_at' => ['nullable', 'date'],
            'hull_condition' => ['nullable', 'integer', 'min:1', 'max:5'],
            'engine_condition' => ['nullable', 'integer', 'min:1', 'max:5'],
            'interior_condition' => ['nullable', 'integer', 'min:1', 'max:5'],
            'systems_condition' => ['nullable', 'integer', 'min:1', 'max:5'],
            'findings' => ['nullable', 'string', 'max:5000'],
            'recommended_works' => ['nullable', 'string', 'max:5000'],
            'estimated_works_cost' => ['nullable', 'numeric', 'min:0'],
            'outcome' => ['nullable', Rule::in(['clear', 'defects', 'failed'])],
            'status' => ['required', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
        ]);
    }
}
