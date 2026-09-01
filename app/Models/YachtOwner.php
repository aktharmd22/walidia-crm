<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtOwnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Ownership can be shared, so this is a record rather than a foreign key.
 *
 * @property int $id
 * @property int $yacht_id
 * @property int $client_id
 * @property numeric $ownership_percentage
 * @property bool $is_primary
 * @property CarbonImmutable|null $since
 * @property CarbonImmutable|null $until
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Client|null $client
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtOwnerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereOwnershipPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereSince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner withoutTrashed()
 *
 * @mixin \Eloquent
 */
class YachtOwner extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtOwnerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ownership_percentage' => 'decimal:2',
            'is_primary' => 'boolean',
            'since' => 'date',
            'until' => 'date',
        ];
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
