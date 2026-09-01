<?php

declare(strict_types=1);

namespace App\Http\Controllers\Brokerage;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\NdaResource;
use App\Models\Client;
use App\Models\Listing;
use App\Models\Nda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The agreement that has to exist before a buyer sees anything.
 *
 * Every viewing reads this record; nothing else about a viewing matters until
 * it says signed.
 *
 * @extends ResourceController<Nda>
 */
class NdaController extends ResourceController
{
    protected string $model = Nda::class;

    protected string $name = 'ndas';

    protected string $pages = 'Brokerage/Ndas';

    protected string $resource = NdaResource::class;

    protected ?string $routePrefix = 'brokerage.ndas';

    protected array $indexWith = ['client:id,full_name', 'listing:id,reference'];

    protected array $showWith = ['client', 'listing'];

    protected array $sortable = ['reference', 'signed_at', 'expires_on', 'status'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'client_id', 'listing_id', 'scope'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Nda::class);

        $record = Nda::create($this->validated($request));

        return redirect()->route('brokerage.ndas.show', $record)->with('success', 'Nda created.');
    }

    public function update(Request $request, Nda $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $record->update($this->validated($request));

        return back()->with('success', 'Nda updated.');
    }

    /** Signature is a fact with a date, not a checkbox. */
    public function markSigned(Request $request, Nda $nda): RedirectResponse
    {
        $this->authorize('markSigned', $nda);

        $data = $request->validate([
            'signed_at' => ['required', 'date', 'before_or_equal:now'],
            'expires_on' => ['nullable', 'date', 'after:signed_at'],
        ]);

        $nda->forceFill($data + ['status' => 'signed'])->save();
        $nda->logActivity('system', 'NDA signed');

        return back()->with('success', 'NDA recorded as signed. Viewings can now be scheduled.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'clients' => Client::orderBy('full_name')->limit(500)->get(['id', 'full_name'])
                ->map(fn (Client $client): array => ['value' => $client->id, 'label' => (string) $client->full_name])
                ->all(),
            'listings' => Listing::with('yacht:id,name')->orderBy('id')->limit(300)->get()
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
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'scope' => ['required', Rule::in(['listing', 'fleet'])],
            'sent_at' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after:today'],
            'status' => ['required', Rule::in(['draft', 'sent', 'signed', 'expired'])],
        ]);
    }
}
