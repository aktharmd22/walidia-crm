<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CrewDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Visas, seaman books, STCW certificates, medicals. Expiry drives both the dispatch gate and the 30-day warning.
 *
 * @property CarbonImmutable|null $expires_on
 * @property string $type
 * @property string $status
 */
class CrewDocument extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CrewDocumentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['number'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'issued_on' => 'date',
        'expires_on' => 'date',
        'verified_at' => 'datetime',
        'number' => 'encrypted',
    ];

    /** @return BelongsTo<Crew, $this> */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class, 'crew_id');
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
}
