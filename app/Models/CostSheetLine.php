<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CostSheetLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * One line of the Cost & Offer table, in one of three phases:
 * quoted, invoiced or actual (D-011).
 *
 * @property string $phase
 * @property string $section
 * @property string $category
 * @property numeric $amount
 */
class CostSheetLine extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CostSheetLineFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'meta' => 'array',
    ];

    /** @return BelongsTo<CostSheet, $this> */
    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class, 'cost_sheet_id');
    }
}
