<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * is_public decides what the website sync is allowed to publish (Q17).
 *
 * @property int $id
 * @property int $yacht_id
 * @property string $collection
 * @property string $disk
 * @property string $path
 * @property string|null $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $alt_en
 * @property string|null $alt_ar
 * @property int $sort_order
 * @property bool $is_public
 * @property int|null $uploaded_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtMediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereAltAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereAltEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia withoutTrashed()
 *
 * @mixin \Eloquent
 */
class YachtMedia extends Model
{
    /** @use HasFactory<YachtMediaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'yacht_media';

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    public function url(): ?string
    {
        if ($this->disk === 'public') {
            return Storage::disk('public')->url($this->path);
        }

        // Private media is served through the policy-checked document route.
        return route('fleet.media.show', ['media' => $this->id]);
    }
}
