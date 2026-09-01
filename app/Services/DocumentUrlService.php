<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Every file in the system passes through here (D-015).
 *
 * Uploads are validated by real content type, stored under a random name on a
 * private disk, and never served directly: downloads are authorised by policy,
 * logged, and then handed out as a five-minute signed URL — or streamed, where
 * the disk is local and cannot sign.
 */
class DocumentUrlService
{
    /** Extensions we accept regardless of what the client claims the file is. */
    private const ALLOWED = [
        'pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic',
        'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip',
    ];

    private const ALLOWED_MIME = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp', 'image/heic',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv', 'text/plain', 'application/zip',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(UploadedFile $file, array $attributes): Document
    {
        $this->guard($file);

        $disk = config('filesystems.private_disk', 'private');
        $path = $file->storeAs(
            $this->directory($attributes),
            $this->filename($file),
            ['disk' => $disk],
        );

        return Document::create($attributes + [
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'version' => 1,
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function addVersion(Document $document, UploadedFile $file, ?string $note = null): Document
    {
        $this->guard($file);

        // The previous file is kept: a superseded contract is still evidence.
        $document->versions()->create([
            'version' => $document->version,
            'path' => $document->path,
            'size' => $document->size,
            'checksum' => $document->checksum,
            'note' => $note,
            'uploaded_by' => auth()->id(),
        ]);

        $path = $file->storeAs(
            $this->directory(['subject_type' => $document->subject_type, 'subject_id' => $document->subject_id]),
            $this->filename($file),
            ['disk' => $document->disk],
        );

        $document->forceFill([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'version' => $document->version + 1,
            'uploaded_by' => auth()->id(),
        ])->save();

        return $document;
    }

    /**
     * Authorisation and logging happen in the policy; by the time we are here
     * the download is allowed.
     */
    public function respond(Document $document): StreamedResponse|RedirectResponse
    {
        $disk = Storage::disk($document->disk);

        abort_unless($disk->exists($document->path), 404, 'The file is no longer in storage.');

        // S3-compatible disks sign; the local disk streams through the app.
        if (config("filesystems.disks.{$document->disk}.driver") === 's3') {
            return redirect()->away($disk->temporaryUrl(
                $document->path,
                now()->addMinutes((int) config('walidia.document_url_ttl_minutes', 5)),
            ));
        }

        return $disk->download($document->path, $document->original_name);
    }

    private function guard(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        abort_unless(in_array($extension, self::ALLOWED, true), 422, 'That file type is not accepted.');

        // Trust the sniffed type, not the extension or the client's claim.
        abort_unless(in_array((string) $file->getMimeType(), self::ALLOWED_MIME, true), 422, 'That file type is not accepted.');

        abort_if($file->getSize() > 25 * 1024 * 1024, 422, 'Files are limited to 25 MB.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function directory(array $attributes): string
    {
        $type = Str::slug((string) ($attributes['subject_type'] ?? 'general'));
        $id = (string) ($attributes['subject_id'] ?? '0');

        return "documents/{$type}/{$id}/".now()->format('Y/m');
    }

    private function filename(UploadedFile $file): string
    {
        // Randomised: the stored name never reveals a client's name or a
        // reference, and cannot be guessed from a URL.
        return Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());
    }
}
