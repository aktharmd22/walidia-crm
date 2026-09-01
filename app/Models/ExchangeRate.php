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
    use AuditableTrait;

    /** @use HasFactory<ExchangeRateFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:8',
        'rate_date' => 'date',
    ];
}
