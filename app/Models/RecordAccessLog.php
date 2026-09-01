<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only. Written whenever a VIP field, a guest manifest or a private
 * document is read or exported. Nothing in the application updates or deletes
 * these rows.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $field_group
 * @property string $action
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property CarbonImmutable $occurred_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereFieldGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class RecordAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'subject_type', 'subject_id', 'field_group',
        'action', 'ip_address', 'user_agent', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
