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
 *
 * @property string $description_en
 * @property string|null $category
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property int|null $yacht_id
 * @property-read Yacht|null $yacht
 */
class ProposalItem extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ProposalItemFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

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
