<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A vessel certificate and the date it dies.
 *
 * An expired certificate that blocks a charter is not paperwork: it is a
 * charter that cannot leave the marina, which is why dispatch reads this.
 */
class Certificate extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'issued_on' => 'date',
        'expires_on' => 'date',
        'blocks_charter' => 'boolean',
    ];

    public function sequenceKey(): string
    {
        return 'certificate';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    public function isExpiring(int $days = 60): bool
    {
        return $this->expires_on !== null && $this->expires_on->isBetween(now(), now()->addDays($days));
    }
}
