<?php

declare(strict_types=1);

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The vessel's paperwork, and the dates it dies.
 *
 * Dispatch reads this table: a charter whose safety certificate expires before
 * it returns does not sail.
 *
 * @extends ResourceController<Certificate>
 */
class CertificateController extends ResourceController
{
    protected string $model = Certificate::class;

    protected string $name = 'certificates';

    protected string $pages = 'Management/Certificates';

    protected string $resource = CertificateResource::class;

    protected ?string $routePrefix = 'management.certificates';

    protected array $indexWith = ['yacht:id,name'];

    protected array $showWith = ['yacht'];

    protected array $sortable = ['reference', 'expires_on', 'type', 'status'];

    protected string $defaultSort = 'expires_on';

    protected array $filterable = ['type', 'status', 'yacht_id', 'blocks_charter'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Certificate::class);

        $record = Certificate::create($this->validated($request));

        return redirect()->route('management.certificates.show', $record)->with('success', 'Saved.');
    }

    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        $this->authorize('update', $certificate);

        $certificate->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    /**
     * The compliance board: everything expiring across the fleet, worst first.
     * This is the screen that stops a charter being cancelled on the morning.
     */
    public function expiry(Request $request): Response
    {
        $this->authorize('viewAny', Certificate::class);

        $days = (int) $request->integer('days', 90);

        $certificates = Certificate::query()
            ->with('yacht:id,name')
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', now()->addDays($days))
            ->orderBy('expires_on')
            ->get();

        return Inertia::render('Management/Certificates/Expiry', [
            'days' => $days,
            'rows' => $certificates->map(fn (Certificate $certificate): array => [
                'id' => $certificate->id,
                'yacht' => $certificate->yacht?->name,
                'yacht_id' => $certificate->yacht_id,
                'name' => $certificate->name,
                'type' => $certificate->type,
                'expires_on' => $certificate->expires_on?->toDateString(),
                'is_expired' => $certificate->isExpired(),
                'blocks_charter' => $certificate->blocks_charter,
                'tone' => $certificate->isExpired() ? 'danger' : 'warning',
                'url' => route('management.certificates.show', $certificate->id),
            ])->all(),
        ]);
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'yacht_id' => ['required', 'integer', 'exists:yachts,id'],
            'type' => ['required', 'string', 'max:48'],
            'name' => ['required', 'string', 'max:190'],
            'number' => ['nullable', 'string', 'max:90'],
            'issued_by' => ['nullable', 'string', 'max:190'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date'],
            'blocks_charter' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['valid', 'expiring', 'expired', 'renewing'])],
        ]);
    }
}
