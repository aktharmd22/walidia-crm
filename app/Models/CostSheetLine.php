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
 */
class CostSheetLine extends Model implements Auditable
{
    /** @use HasFactory<CostSheetLineFactory> */
    use AuditableTrait;

    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'amount' => 'decimal:2', 'tax_rate' => 'decimal:2', 'tax_amount' => 'decimal:2', 'is_taxable' => 'boolean', 'meta' => 'array'];
    }

    /** @return BelongsTo<CostSheet, $this> */
    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class, 'cost_sheet_id');
    }
}
