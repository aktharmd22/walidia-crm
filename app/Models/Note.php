<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property string $body
 * @property bool $is_internal
 * @property bool $is_vip
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Model $subject
 * @property-read User|null $user
 *
 * @method static \Database\Factories\NoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereIsVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Note extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['body'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_internal' => 'boolean',
        'is_vip' => 'boolean',
    ];

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
