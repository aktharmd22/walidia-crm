<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\ClientContactFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * A PA, family office or captain who contacts us on the principal's behalf.
 *
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $role
 * @property string|null $email
 * @property string|null $mobile
 * @property bool $is_primary
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Client|null $client
 * @property-read User|null $creator
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\ClientContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact withoutTrashed()
 *
 * @mixin \Eloquent
 */
class ClientContact extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ClientContactFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
