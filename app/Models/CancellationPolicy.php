<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CancellationPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Cancellation tiers as data: days before departure to fee percentage (Q10).
 *
 * @property list<array{days_before: int, fee_pct: float}>|null $rules
 */
class CancellationPolicy extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CancellationPolicyFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'rules' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * The fee percentage that applies this many days before departure.
     */
    public function feePercentageFor(int $daysBefore): float
    {
        $applicable = 100.0;

        foreach ($this->rules ?? [] as $tier) {
            if ($daysBefore >= (int) ($tier['days_before'] ?? 0)) {
                $applicable = min($applicable, (float) ($tier['fee_pct'] ?? 100));
            }
        }

        return $applicable;
    }
}
