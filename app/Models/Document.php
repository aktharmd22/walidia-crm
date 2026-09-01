<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * The vault. Files live on a private disk and are never addressable without a
 * policy check plus a short-lived signed URL (D-015).
 *
 * @property int $id
 * @property string|null $reference
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $category
 * @property string $title
 * @property string|null $description
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $checksum
 * @property int $version
 * @property CarbonImmutable|null $issued_on
 * @property CarbonImmutable|null $expires_on
 * @property int $reminder_days
 * @property string $visibility
 * @property bool $is_sensitive
 * @property bool $requires_signature
 * @property CarbonImmutable|null $signed_at
 * @property string $status
 * @property int|null $uploaded_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read User|null $creator
 * @property-read Collection<int, SignatureRequest> $signatureRequests
 * @property-read int|null $signature_requests_count
 * @property-read Model|null $subject
 * @property-read User|null $updater
 * @property-read User|null $uploader
 * @property-read Collection<int, DocumentVersion> $versions
 * @property-read int|null $versions_count
 *
 * @method static Builder<static>|Document expiringWithin(int $days)
 * @method static \Database\Factories\DocumentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Document newModelQuery()
 * @method static Builder<static>|Document newQuery()
 * @method static Builder<static>|Document onlyTrashed()
 * @method static Builder<static>|Document query()
 * @method static Builder<static>|Document search(string $term)
 * @method static Builder<static>|Document whereCategory($value)
 * @method static Builder<static>|Document whereChecksum($value)
 * @method static Builder<static>|Document whereCreatedAt($value)
 * @method static Builder<static>|Document whereCreatedBy($value)
 * @method static Builder<static>|Document whereDeletedAt($value)
 * @method static Builder<static>|Document whereDescription($value)
 * @method static Builder<static>|Document whereDisk($value)
 * @method static Builder<static>|Document whereExpiresOn($value)
 * @method static Builder<static>|Document whereId($value)
 * @method static Builder<static>|Document whereIsSensitive($value)
 * @method static Builder<static>|Document whereIssuedOn($value)
 * @method static Builder<static>|Document whereMime($value)
 * @method static Builder<static>|Document whereOriginalName($value)
 * @method static Builder<static>|Document wherePath($value)
 * @method static Builder<static>|Document whereReference($value)
 * @method static Builder<static>|Document whereReminderDays($value)
 * @method static Builder<static>|Document whereRequiresSignature($value)
 * @method static Builder<static>|Document whereSignedAt($value)
 * @method static Builder<static>|Document whereSize($value)
 * @method static Builder<static>|Document whereStatus($value)
 * @method static Builder<static>|Document whereSubjectId($value)
 * @method static Builder<static>|Document whereSubjectType($value)
 * @method static Builder<static>|Document whereTitle($value)
 * @method static Builder<static>|Document whereUpdatedAt($value)
 * @method static Builder<static>|Document whereUpdatedBy($value)
 * @method static Builder<static>|Document whereUploadedBy($value)
 * @method static Builder<static>|Document whereVersion($value)
 * @method static Builder<static>|Document whereVisibility($value)
 * @method static Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Document withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Document extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'expires_on' => 'date',
            'signed_at' => 'datetime',
            'is_sensitive' => 'boolean',
            'requires_signature' => 'boolean',
        ];
    }

    public function sequenceKey(): string
    {
        return 'document';
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<DocumentVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    /** @return HasMany<SignatureRequest, $this> */
    public function signatureRequests(): HasMany
    {
        return $this->hasMany(SignatureRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    public function isExpiring(int $days = 30): bool
    {
        return $this->expires_on !== null
            && ! $this->isExpired()
            && $this->expires_on->lte(now()->addDays($days));
    }

    /**
     * @param  Builder<Document>  $query
     * @return Builder<Document>
     */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->whereNotNull('expires_on')
            ->whereDate('expires_on', '>=', now())
            ->whereDate('expires_on', '<=', now()->addDays($days));
    }

    /**
     * @param  Builder<Document>  $query
     * @return Builder<Document>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)
                ->orWhere('original_name', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
