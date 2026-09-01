<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\YachtResource;
use App\Models\Marina;
use App\Models\Yacht;
use App\Models\YachtAvailabilityBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Yacht>
 */
class YachtController extends ResourceController
{
    protected string $model = Yacht::class;

    protected string $name = 'yachts';

    protected string $pages = 'Fleet';

    protected string $resource = YachtResource::class;

    protected ?string $routePrefix = 'fleet.yachts';

    protected array $indexWith = ['homeMarina:id,name,timezone', 'charterProfile', 'saleProfile', 'media'];

    protected array $showWith = ['homeMarina', 'berth', 'charterProfile', 'saleProfile', 'managementProfile', 'media', 'owners', 'inventory'];

    protected array $sortable = ['name', 'reference', 'loa_m', 'year_built', 'status', 'created_at'];

    protected string $defaultSort = 'name';

    protected array $filterable = ['status', 'role', 'home_marina_id', 'builder'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Yacht::class);

        $data = $this->validated($request);

        $yacht = DB::transaction(function () use ($data): Yacht {
            $yacht = Yacht::create($data);

            // A yacht that is in the charter fleet needs somewhere to hold its
            // rates from the moment it exists, or the first proposal has
            // nothing to read.
            if ($yacht->is_charter_fleet) {
                $yacht->charterProfile()->create(['currency' => config('walidia.currency')]);
            }

            if ($yacht->is_for_sale) {
                $yacht->saleProfile()->create(['currency' => config('walidia.currency')]);
            }

            if ($yacht->is_managed) {
                $yacht->managementProfile()->create([]);
            }

            $yacht->logActivity('system', 'Yacht added to the registry');

            return $yacht;
        });

        return redirect()->route('fleet.yachts.show', $yacht)
            ->with('success', "{$yacht->name} added as {$yacht->reference}.");
    }

    public function update(Request $request, Yacht $yacht): RedirectResponse
    {
        $this->authorize('update', $yacht);

        $data = $this->validated($request, $yacht);

        DB::transaction(function () use ($yacht, $data): void {
            $yacht->update($data);

            if ($yacht->is_charter_fleet && $yacht->charterProfile === null) {
                $yacht->charterProfile()->create(['currency' => config('walidia.currency')]);
            }

            if ($yacht->is_for_sale && $yacht->saleProfile === null) {
                $yacht->saleProfile()->create(['currency' => config('walidia.currency')]);
            }

            if ($yacht->is_managed && $yacht->managementProfile === null) {
                $yacht->managementProfile()->create([]);
            }
        });

        return back()->with('success', 'Yacht updated.');
    }

    /* ── scoped lists ───────────────────────────────────────────────────── */

    public function charterFleet(Request $request): Response
    {
        return $this->scoped($request, fn (Builder $query) => $query->charterFleet(), 'Charter Fleet');
    }

    public function forSale(Request $request): Response
    {
        return $this->scoped($request, fn (Builder $query) => $query->forSale(), 'For Sale');
    }

    public function managed(Request $request): Response
    {
        return $this->scoped($request, fn (Builder $query) => $query->managed(), 'Managed Yachts');
    }

    /** The fleet calendar: one row per yacht, one bar per availability block. */
    public function availability(Request $request): Response
    {
        $this->authorize('viewAny', Yacht::class);

        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->addMonths(2)->endOfMonth();

        $yachts = Yacht::query()
            ->where('status', 'active')
            ->with(['availabilityBlocks' => fn ($query) => $query->effective()->overlapping($from, $to)])
            ->orderBy('name')
            ->get();

        return Inertia::render('Fleet/Availability', [
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            /** @phpstan-ignore-next-line the payload shape is asserted by the Inertia page props */
            'yachts' => $yachts->map(fn (Yacht $yacht): array => [
                'id' => $yacht->id,
                'name' => $yacht->name,
                'reference' => $yacht->reference,
                'blocks' => $yacht->availabilityBlocks->map(fn (YachtAvailabilityBlock $block): array => [
                    'id' => $block->id,
                    'type' => $block->type,
                    'tone' => match ($block->type) {
                        'booking' => 'info',
                        'option_hold' => 'warning',
                        'maintenance' => 'attention',
                        'owner_use' => 'neutral',
                        default => 'neutral',
                    },
                    'starts_at' => $block->starts_at->toIso8601String(),
                    'ends_at' => $block->ends_at->toIso8601String(),
                    'expires_at' => $block->expires_at?->toIso8601String(),
                    'note' => $block->note,
                ]),
            ]),
        ]);
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    /**
     * @param  Builder<Yacht>  $query
     */
    protected function filterRole(Builder $query, string $value): void
    {
        match ($value) {
            'charter' => $query->charterFleet(),
            'sale' => $query->forSale(),
            'managed' => $query->managed(),
            default => null,
        };
    }

    /**
     * @param  callable(Builder<Yacht>): Builder<Yacht>  $modifier
     */
    private function scoped(Request $request, callable $modifier, string $heading): Response
    {
        $this->authorize('viewAny', Yacht::class);

        return Inertia::render('Fleet/Index', [
            'rows' => $this->paginate($request, $modifier),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => $heading,
        ]);
    }

    protected function indexProps(Request $request): array
    {
        return ['heading' => 'Fleet'];
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'marinas' => Marina::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (Marina $marina): array => ['value' => $marina->id, 'label' => $marina->name]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Yacht $yacht = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'name_ar' => ['nullable', 'string', 'max:190'],
            'is_charter_fleet' => ['boolean'],
            'is_for_sale' => ['boolean'],
            'is_managed' => ['boolean'],
            'builder' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'year_built' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'year_refit' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'loa_m' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'beam_m' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'draft_m' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'gross_tonnage' => ['nullable', 'integer', 'min:0'],
            'engines' => ['nullable', 'string', 'max:190'],
            'engine_hours' => ['nullable', 'integer', 'min:0'],
            'cruising_speed_kn' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_speed_kn' => ['nullable', 'integer', 'min:0', 'max:100'],
            // Static capacity is a licensing limit; cruising capacity can never
            // exceed it, and the operations side depends on that holding.
            'capacity_static' => ['nullable', 'integer', 'min:1', 'max:500'],
            'capacity_cruising' => ['nullable', 'integer', 'min:1', 'max:500', 'lte:capacity_static'],
            'cabins' => ['nullable', 'integer', 'min:0', 'max:50'],
            'berths' => ['nullable', 'integer', 'min:0', 'max:100'],
            'crew_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flag_country' => ['nullable', 'string', 'max:90'],
            'registration_no' => ['nullable', 'string', 'max:64'],
            'imo_no' => ['nullable', 'string', 'max:32'],
            'mmsi' => ['nullable', 'string', 'max:32'],
            'home_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'owner_client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'maintenance', 'off_market', 'sold', 'archived'])],
        ]);
    }
}
