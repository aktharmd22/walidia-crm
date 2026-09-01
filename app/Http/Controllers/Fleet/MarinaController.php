<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\MarinaResource;
use App\Models\Marina;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @extends ResourceController<Marina>
 */
class MarinaController extends ResourceController
{
    protected string $model = Marina::class;

    protected string $name = 'marinas';

    protected string $pages = 'Marinas';

    protected string $resource = MarinaResource::class;

    protected ?string $routePrefix = 'fleet.marinas';

    protected array $sortable = ['name', 'country', 'city', 'created_at'];

    protected string $defaultSort = 'name';

    protected array $filterable = ['country', 'is_active'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Marina::class);

        $marina = Marina::create($this->validated($request));

        return redirect()->route('fleet.marinas.show', $marina)->with('success', "{$marina->name} added.");
    }

    public function update(Request $request, Marina $marina): RedirectResponse
    {
        $this->authorize('update', $marina);

        $marina->update($this->validated($request));

        return back()->with('success', 'Marina updated.');
    }

    protected function baseQuery(Request $request): Builder
    {
        return Marina::query()->withCount(['berths', 'yachts']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'name_ar' => ['nullable', 'string', 'max:190'],
            'country' => ['required', 'string', 'max:90'],
            'emirate' => ['nullable', 'string', 'max:90'],
            'city' => ['nullable', 'string', 'max:90'],
            // The timezone is load-bearing: charter instants are derived from
            // the departure marina, not assumed to be Asia/Dubai (D-010).
            'timezone' => ['required', 'string', 'timezone'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email:rfc', 'max:190'],
            'requires_manifest' => ['boolean'],
            'manifest_format' => ['nullable', Rule::in(['pdf', 'csv', 'xlsx'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);
    }
}
