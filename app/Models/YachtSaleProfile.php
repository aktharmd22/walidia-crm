<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtSaleProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $yacht_id
 * @property numeric|null $asking_price
 * @property string $currency
 * @property string $price_visibility
 * @property string|null $vat_status
 * @property bool $is_price_negotiable
 * @property int|null $last_valuation_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtSaleProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereAskingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereIsPriceNegotiable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereLastValuationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile wherePriceVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereVatStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereYachtId($value)
 *
 * @mixin \Eloquent
 */
class YachtSaleProfile extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtSaleProfileFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'asking_price' => 'decimal:2',
        'is_price_negotiable' => 'boolean',
    ];

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
