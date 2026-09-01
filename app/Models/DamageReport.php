<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\DamageReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Damage found after a charter.
 *
 * Closing the inspection is what releases the security deposit — that is a
 * hard gate, and this record is what it reads.
 *
 * @property string $inspection_status
 * @property CarbonImmutable|null $closed_at
 * @property bool $deduct_from_deposit
 * @property numeric|null $actual_cost
 */
class DamageReport extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<DamageReportFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'discovered_at' => 'datetime',
        'closed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'deduct_from_deposit' => 'boolean',
        'photo_paths' => 'array',
    ];

    public function sequenceKey(): string
    {
        return 'damage';
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    public function isClosed(): bool
    {
        return $this->inspection_status === 'closed';
    }
}
