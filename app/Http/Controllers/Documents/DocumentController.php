<?php

declare(strict_types=1);

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentUrlService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The vault.
 *
 * @extends ResourceController<Document>
 */
class DocumentController extends ResourceController
{
    protected string $model = Document::class;

    protected string $name = 'documents';

    protected string $pages = 'Documents';

    protected string $resource = DocumentResource::class;

    protected array $indexWith = ['uploader:id,name'];

    protected array $showWith = ['uploader', 'versions.uploader', 'signatureRequests'];

    protected array $sortable = ['title', 'category', 'expires_on', 'created_at'];

    protected array $filterable = ['category', 'status', 'visibility', 'subject_type'];

    public function store(Request $request, DocumentUrlService $documents): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:25600'],  // 25 MB (brief §4)
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['kyc', 'contract', 'certificate', 'invoice', 'proposal', 'survey', 'statement', 'other'])],
            'subject_type' => ['nullable', 'string', 'max:64'],
            'subject_id' => ['nullable', 'integer'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after:issued_on'],
            'visibility' => ['required', Rule::in(['internal', 'client', 'owner', 'portal'])],
            'is_sensitive' => ['boolean'],
            'requires_signature' => ['boolean'],
        ]);

        $document = $documents->store($request->file('file'), $data);

        return back()->with('success', "{$document->title} uploaded.");
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->update($request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:48'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date'],
            'visibility' => ['required', Rule::in(['internal', 'client', 'owner', 'portal'])],
            'is_sensitive' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'superseded', 'expired', 'void'])],
        ]));

        return back()->with('success', 'Document updated.');
    }

    /** Uploads a new version rather than overwriting evidence. */
    public function addVersion(Request $request, Document $document, DocumentUrlService $documents): RedirectResponse
    {
        $this->authorize('update', $document);

        $request->validate([
            'file' => ['required', 'file', 'max:25600'],
            'note' => ['nullable', 'string', 'max:190'],
        ]);

        DB::transaction(fn () => $documents->addVersion($document, $request->file('file'), $request->input('note')));

        return back()->with('success', "Version {$document->fresh()->version} uploaded.");
    }

    /**
     * Authorise, log, then hand out a five-minute signed URL. No file in this
     * system is ever addressable without passing through here (D-015).
     */
    public function download(Request $request, Document $document, DocumentUrlService $documents): StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $document);

        return $documents->respond($document);
    }

    public function pendingSignature(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        return Inertia::render('Documents/Index', [
            'rows' => $this->paginate(
                $request,
                fn (Builder $query) => $query->where('requires_signature', true)->whereNull('signed_at'),
            ),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => 'Pending Signature',
        ]);
    }

    public function expiry(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $days = (int) $request->integer('days', 60);

        return Inertia::render('Documents/Index', [
            'rows' => $this->paginate(
                $request,
                fn (Builder $query) => $query->where(function (Builder $query) use ($days): void {
                    $query->expiringWithin($days)->orWhere('expires_on', '<', now());
                })->reorder('expires_on'),
            ),
            'filters' => $this->currentFilters($request),
            'can' => $this->abilities($request),
            'heading' => "Expiring within {$days} days",
        ]);
    }

    protected function indexProps(Request $request): array
    {
        return ['heading' => 'Document Vault'];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var Document $record */
        return [
            'versions' => $record->versions->map(fn ($version): array => [
                'id' => $version->id,
                'version' => $version->version,
                'note' => $version->note,
                'size' => $version->size,
                'uploader' => $version->uploader?->name,
                'created_at' => $version->created_at?->toIso8601String(),
            ]),
            'exists' => Storage::disk($record->disk)->exists($record->path),
        ];
    }
}
