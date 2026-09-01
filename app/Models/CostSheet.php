<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\CostSheetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The Cost & Offer table as one object with three phases (D-011).
 *
 * Quote → invoice → actuals → P&L is a single artifact, exactly as the client
 * already works. Splitting it into three documents loses the variance analysis
 * that makes it worth keeping.
 */
class CostSheet extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CostSheetFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** Categories from the client's own Cost & Offer sheet. */
    public const REVENUE_CATEGORIES = [
        'hourly_rate' => 'Hourly rate',
        'yacht_fee' => 'Yacht fee',
        'tax' => 'Tax',
        'visitor_fee' => 'Visitor fees',
        'berth_fee' => 'Berth fees',
        'security_deposit' => 'Security deposit',
        'food' => 'Food',
        'beverages' => 'Beverages',
        'entertainment' => 'Entertainment',
        'watersports' => 'Watersports',
        'transfers' => 'Transfers',
        'other_revenue' => 'Other',
    ];

    public const COST_CATEGORIES = [
        'operations_staff' => 'Operations staff',
        'buggy_driver_tips' => 'Buggy driver tips',
        'catering_tips' => 'Catering tips',
        'crew_tips' => 'Crew tips',
        'team_commission' => 'Team commission',
        'agent_commission' => 'Agent commission',
        'bank_charges' => 'Bank charges + VAT',
        'apa_refund' => 'APA refund',
        'catering_cost' => 'Catering cost',
        'fuel' => 'Fuel',
        'vendor_cost' => 'Vendor cost',
        'other_cost' => 'Other',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'total_offer' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'margin_pct' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function sequenceKey(): string
    {
        return 'cost_sheet';
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return HasMany<CostSheetLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(CostSheetLine::class)->orderBy('sort_order');
    }

    /**
     * Lines for one phase. P&L reads `actual` and falls back to `invoiced`,
     * so a charter that has not been reconciled yet still shows a number.
     *
     * @return Collection<int, CostSheetLine>
     */
    public function linesFor(string $phase): Collection
    {
        return $this->lines->where('phase', $phase)->values();
    }

    public function effectivePhase(): string
    {
        foreach (['actual', 'invoiced', 'quoted'] as $phase) {
            if ($this->lines->where('phase', $phase)->isNotEmpty()) {
                return $phase;
            }
        }

        return 'quoted';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Which phase a given user may write. Sales prices the quote; Finance owns
     * what was invoiced and what actually happened.
     */
    public function writablePhasesFor(User $user): array
    {
        if ($this->isClosed()) {
            return [];
        }

        $phases = [];

        if ($user->can('cost-sheets.update')) {
            $phases[] = 'quoted';
        }

        if ($user->can('finance.view-amounts')) {
            $phases[] = 'invoiced';
            $phases[] = 'actual';
        }

        return array_values(array_unique($phases));
    }
}
