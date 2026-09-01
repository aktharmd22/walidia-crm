<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\DocumentVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property int $version
 * @property string $path
 * @property int $size
 * @property string|null $checksum
 * @property string|null $note
 * @property int|null $uploaded_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Document|null $document
 * @property-read User|null $uploader
 *
 * @method static \Database\Factories\DocumentVersionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereVersion($value)
 *
 * @mixin \Eloquent
 */
class DocumentVersion extends Model
{
    /** @use HasFactory<DocumentVersionFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
