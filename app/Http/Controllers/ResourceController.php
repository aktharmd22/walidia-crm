<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Paginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The shared CRUD spine.
 *
 * Every business entity exposes index, create, store, show, edit, update,
 * archive and restore, plus bulk actions and export (D-018, D-019). Writing
 * that sixty times by hand guarantees sixty slightly different behaviours, so
 * it is written once here and specialised by subclasses.
 *
 * Controllers stay thin: validation lives in Form Requests, authorisation in
 * Policies, and anything with real business consequence in an Action class.
 *
 * @template TModel of Model
 */
abstract class ResourceController extends Controller
{
    /** @var class-string<TModel> */
    protected string $model;

    /** Permission prefix and route name prefix, e.g. 'clients'. */
    protected string $name;

    /** Inertia page directory, e.g. 'Clients'. */
    protected string $pages;

    /**
     * API Resource shaping the payload sent to Inertia. One shape per entity,
     * so a field hidden by permission is never accidentally serialised on a
     * screen that forgot to hide it.
     *
     * @var class-string<JsonResource>
     */
    protected string $resource;

    /** Lighter resource for list screens, when the detail shape is too heavy. */
    protected ?string $listResource = null;

    /** Route name prefix if it differs from $name, e.g. 'charter.bookings'. */
    protected ?string $routePrefix = null;

    /** @var list<string> Relations eager-loaded on the index — never leave this to chance. */
    protected array $indexWith = [];

    /** @var list<string> Relations eager-loaded on the detail screen. */
    protected array $showWith = [];

    /** @var list<string> Columns the ?sort= parameter may address. */
    protected array $sortable = ['created_at', 'updated_at'];

    protected string $defaultSort = '-created_at';

    /** @var list<string> Filter keys accepted from the query string. */
    protected array $filterable = [];

    protected int $perPage = 25;

    /* ── the eight ──────────────────────────────────────────────────────── */

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', $this->model);

        return Inertia::render("{$this->pages}/Index", array_merge([
            'rows' => $this->paginate($request),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
        ], $this->indexProps($request)));
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', $this->model);

        return Inertia::render("{$this->pages}/Create", $this->formProps($request));
    }

    public function show(Request $request, int|string $id): Response
    {
        $record = $this->findRecord($id);

        $this->authorize('view', $record);

        return Inertia::render("{$this->pages}/Show", array_merge([
            'record' => $this->showResource($record->load($this->showWith)),
            'can' => $this->recordAbilities($request, $record),
        ], $this->showProps($request, $record)));
    }

    public function edit(Request $request, int|string $id): Response
    {
        $record = $this->findRecord($id);

        $this->authorize('update', $record);

        return Inertia::render("{$this->pages}/Edit", array_merge([
            'record' => $this->showResource($record),
        ], $this->formProps($request, $record)));
    }

    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        $record = $this->findRecord($id);

        $this->authorize('delete', $record);

        $record->delete();

        return back()->with('success', $this->label().' archived. You can restore it from the archive.');
    }

    public function restore(Request $request, int|string $id): RedirectResponse
    {
        /** @var TModel $record */
        $record = $this->model::withTrashed()->findOrFail($id);

        $this->authorize('restore', $record);

        $record->restore();

        return back()->with('success', $this->label().' restored.');
    }

    /**
     * Resolves a record through the model's own query, so the ownership global
     * scope applies: a record outside the visible set is a 404, not a 403 —
     * a 403 would confirm that it exists (D-017).
     */
    protected function findRecord(int|string $id): Model
    {
        return $this->model::findOrFail($id);
    }

    public function archive(Request $request): Response
    {
        $this->authorize('viewAny', $this->model);

        return Inertia::render("{$this->pages}/Archive", [
            'rows' => $this->paginate($request, fn (Builder $query) => $query->onlyTrashed()),
            'filters' => $this->currentFilters($request),
        ]);
    }

    /* ── bulk and export ────────────────────────────────────────────────── */

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:assign,archive,restore,status,tag'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'value' => ['nullable'],
        ]);

        $records = $this->model::withTrashed()->whereIn('id', $data['ids'])->get();
        $applied = 0;

        DB::transaction(function () use ($records, $data, &$applied): void {
            foreach ($records as $record) {
                // Each row goes through the same policy as the single-record
                // action: a bulk operation is not a way around authorisation.
                if (! $this->authorizeBulk($data['action'], $record)) {
                    continue;
                }

                $this->applyBulk($data['action'], $record, $data['value'] ?? null);
                $applied++;
            }
        });

        $skipped = $records->count() - $applied;

        return back()->with(
            $skipped > 0 ? 'warning' : 'success',
            $skipped > 0
                ? "{$applied} updated, {$skipped} skipped — you do not have access to those records."
                : "{$applied} records updated.",
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', $this->model);

        $filename = Str::slug($this->name).'-'.now()->format('Y-m-d-His').'.csv';
        $query = $this->filtered($this->baseQuery($request), $request);

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');
            $headerWritten = false;

            $query->chunk(500, function ($chunk) use ($handle, &$headerWritten): void {
                foreach ($chunk as $record) {
                    $row = $this->exportRow($record);

                    if (! $headerWritten) {
                        fputcsv($handle, array_keys($row));
                        $headerWritten = true;
                    }

                    fputcsv($handle, array_values($row));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ── query building ─────────────────────────────────────────────────── */

    /**
     * A page of records in the shape the front end is written against
     * (App\Support\Paginate): data, links, and the counters under `meta`.
     *
     * @param  callable(Builder<TModel>): Builder<TModel>|null  $modifier
     * @return array<string, mixed>
     */
    protected function paginate(Request $request, ?callable $modifier = null): array
    {
        $query = $this->baseQuery($request)->with($this->indexWith);

        if ($modifier !== null) {
            $query = $modifier($query);
        }

        $query = $this->sorted($this->filtered($query, $request), $request);

        $paginator = $query->paginate(
            min((int) $request->integer('per_page', $this->perPage), 100),
        )->withQueryString();

        return Paginate::shape($paginator->through(fn (Model $record) => $this->indexResource($record)));
    }

    /**
     * @return Builder<TModel>
     */
    protected function baseQuery(Request $request): Builder
    {
        return $this->model::query();
    }

    /**
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function filtered(Builder $query, Request $request): Builder
    {
        if ($search = trim((string) $request->query('search', ''))) {
            $query->search($search);
        }

        foreach ($this->filterable as $filter) {
            $value = $request->query($filter);

            if ($value === null || $value === '' || $value === 'all') {
                continue;
            }

            $method = 'filter'.Str::studly($filter);

            if (method_exists($this, $method)) {
                $this->{$method}($query, $value);
            } else {
                $query->where($filter, $value);
            }
        }

        if ($request->boolean('mine')) {
            $query->where('assigned_user_id', $request->user()->id);
        }

        return $query;
    }

    /**
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function sorted(Builder $query, Request $request): Builder
    {
        $sort = (string) $request->query('sort', $this->defaultSort);
        $descending = str_starts_with($sort, '-');
        $column = ltrim($sort, '-');

        if (! in_array($column, $this->sortable, true)) {
            $sort = $this->defaultSort;
            $descending = str_starts_with($sort, '-');
            $column = ltrim($sort, '-');
        }

        return $query->orderBy($column, $descending ? 'desc' : 'asc');
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentFilters(Request $request): array
    {
        return array_merge(
            ['search' => $request->query('search', ''), 'sort' => $request->query('sort', $this->defaultSort)],
            $request->only($this->filterable),
            ['mine' => $request->boolean('mine')],
        );
    }

    /* ── hooks for subclasses ───────────────────────────────────────────── */

    /** @return array<string, mixed> */
    protected function indexProps(Request $request): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    protected function showProps(Request $request, Model $record): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    protected function indexResource(Model $record): array
    {
        $class = $this->listResource ?? $this->resource;

        return $class::make($record)->resolve();
    }

    /** @return array<string, mixed> */
    protected function showResource(Model $record): array
    {
        return ($this->resource)::make($record)->resolve();
    }

    /** @return array<string, scalar|null> */
    protected function exportRow(Model $record): array
    {
        /** @var array<string, scalar|null> $row */
        $row = array_filter(
            $this->indexResource($record),
            fn ($value): bool => is_scalar($value) || $value === null,
        );

        return $row;
    }

    protected function applyBulk(string $action, Model $record, mixed $value): void
    {
        match ($action) {
            'assign' => $record->forceFill(['assigned_user_id' => $value === null ? null : (int) $value])->save(),
            'status' => $record->forceFill(['status' => (string) $value])->save(),
            'archive' => $record->delete(),
            'restore' => $record->restore(),
            'tag' => $this->applyTag($record, (int) $value),
            default => null,
        };
    }

    protected function authorizeBulk(string $action, Model $record): bool
    {
        return match ($action) {
            'assign' => request()->user()->can('reassign', $record),
            'archive' => request()->user()->can('delete', $record),
            'restore' => request()->user()->can('restore', $record),
            default => request()->user()->can('update', $record),
        };
    }

    protected function applyTag(Model $record, int $tagId): void
    {
        DB::table('taggables')->updateOrInsert([
            'tag_id' => $tagId,
            'taggable_type' => $record->getMorphClass(),
            'taggable_id' => $record->getKey(),
        ], ['created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * What the current user may do, so the React layer knows what to render.
     * It still never decides what is allowed — the server does that on every
     * request.
     *
     * @return array<string, bool>
     */
    protected function abilities(Request $request): array
    {
        $user = $request->user();

        return [
            'create' => $user->can('create', $this->model),
            'export' => $user->can("{$this->name}.export"),
            'import' => $user->can("{$this->name}.import"),
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function recordAbilities(Request $request, Model $record): array
    {
        $user = $request->user();

        return [
            'update' => $user->can('update', $record),
            'delete' => $user->can('delete', $record),
            'reassign' => $user->can('reassign', $record),
        ];
    }

    protected function label(): string
    {
        return Str::headline(Str::singular($this->name));
    }

    protected function routeName(string $action): string
    {
        return ($this->routePrefix ?? $this->name).'.'.$action;
    }
}
