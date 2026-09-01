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
 */
class CancellationPolicy extends Model implements Auditable
{
    /** @use HasFactory<CancellationPolicyFactory> */
    use AuditableTrait;

    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['rules' => 'array', 'is_default' => 'boolean'];
    }

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
