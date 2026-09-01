<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CompanyContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $client_id
 * @property string $name
 * @property string|null $position
 * @property string|null $email
 * @property string|null $mobile
 * @property bool $is_primary
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Client|null $client
 * @property-read Company|null $company
 *
 * @method static \Database\Factories\CompanyContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CompanyContact extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CompanyContactFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
