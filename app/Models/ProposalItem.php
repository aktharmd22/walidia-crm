<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProposalItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A line on a proposal. Totals are computed, never posted from the browser.
 */
class ProposalItem extends Model implements Auditable
{
    /** @use HasFactory<ProposalItemFactory> */
    use AuditableTrait;

    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'tax_rate' => 'decimal:2', 'tax_amount' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    /** @return BelongsTo<CharterProposal, $this> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CharterProposal::class, 'charter_proposal_id');
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
