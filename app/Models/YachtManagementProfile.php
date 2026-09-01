<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\YachtManagementProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $yacht_id
 * @property int|null $agreement_id
 * @property int|null $technical_manager_id
 * @property numeric|null $budget_annual
 * @property string $reporting_cadence
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $technicalManager
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\YachtManagementProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereAgreementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereBudgetAnnual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereReportingCadence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereTechnicalManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereYachtId($value)
 *
 * @mixin \Eloquent
 */
class YachtManagementProfile extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<YachtManagementProfileFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['budget_annual' => 'decimal:2'];
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<User, $this> */
    public function technicalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_manager_id');
    }
}
