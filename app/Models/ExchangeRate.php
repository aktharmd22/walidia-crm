<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The rate captured at transaction date, with who captured it (D-002).
 */
class ExchangeRate extends Model implements Auditable
{
    /** @use HasFactory<ExchangeRateFactory> */
    use AuditableTrait;

    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['rate' => 'decimal:8', 'rate_date' => 'date'];
    }
}
