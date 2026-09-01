<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\SurveyResource;
use App\Models\Listing;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Condition survey, sea trial or valuation — and the findings that renegotiate a price.
 *
 * @extends ResourceController<Survey>
 */
class SurveyController extends ResourceController
{
    protected string $model = Survey::class;

    protected string $name = 'surveys';

    protected string $pages = 'Brokerage/Surveys';

    protected string $resource = SurveyResource::class;

    protected ?string $routePrefix = 'brokerage.surveys';

    protected array $indexWith = ['listing:id,reference'];

    protected array $showWith = ['listing.yacht', 'offer.client'];

    protected array $sortable = ['reference', 'scheduled_at', 'status'];

    protected string $defaultSort = '-scheduled_at';

    protected array $filterable = ['status', 'type', 'listing_id', 'outcome'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Survey::class);

        $record = Survey::create($this->validated($request));

        return redirect()->route('brokerage.surveys.show', $record)->with('success', 'Survey created.');
    }

    public function update(Request $request, Survey $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Survey updated.');
    }

    /** The findings are the point: they are what reopens the price. */
    public function record(Request $request, Survey $survey): RedirectResponse
    {
        $this->authorize('update', $survey);

        $data = $request->validate([
            'outcome' => ['required', Rule::in(['clear', 'defects', 'failed'])],
            'findings' => ['required', 'string', 'max:5000'],
            'remediation_estimate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $survey->forceFill($data + ['status' => 'completed', 'completed_at' => now()])->save();
        $survey->logActivity('note', "Survey outcome: {$data['outcome']}", $data['findings']);

        return back()->with('success', 'Survey findings recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
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
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'type' => ['required', Rule::in(['condition', 'sea_trial', 'valuation'])],
            'surveyor_name' => ['nullable', 'string', 'max:190'],
            'surveyor_company' => ['nullable', 'string', 'max:190'],
            'scheduled_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'paid_by' => ['required', Rule::in(['buyer', 'seller', 'shared'])],
            'status' => ['required', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
        ]);
    }
}
