<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VatRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Never hardcode 5%: the rate, the treatment and the dates it applies
 * are data the finance team can correct without a deployment (Q5).
 */
class VatRate extends Model implements Auditable
{
    /** @use HasFactory<VatRateFactory> */
    use AuditableTrait;

    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['rate_pct' => 'decimal:2', 'effective_from' => 'date', 'effective_to' => 'date', 'is_default' => 'boolean'];
    }
}
