<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtInventoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $yacht_id
 * @property string $category
 * @property string $name
 * @property int $quantity
 * @property string $condition
 * @property CarbonImmutable|null $last_checked_at
 * @property int|null $checked_by
 * @property string|null $notes
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtInventoryItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCheckedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereLastCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem withoutTrashed()
 *
 * @mixin \Eloquent
 */
class YachtInventoryItem extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtInventoryItemFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_checked_at' => 'date',
    ];

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
