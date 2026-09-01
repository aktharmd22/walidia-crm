<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CharterDayLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The log of the day itself.
 *
 * Append-only: a correction is a new entry pointing at the original, because
 * an operations log that can be rewritten is not a log.
 *
 * @property CarbonImmutable $occurred_at
 * @property string $event_type
 */
class CharterDayLog extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CharterDayLogFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'occurred_at' => 'datetime',
        'synced_at' => 'datetime',
        'meta' => 'array',
        'photo_paths' => 'array',
    ];

    /** @return BelongsTo<User, $this> */
    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
