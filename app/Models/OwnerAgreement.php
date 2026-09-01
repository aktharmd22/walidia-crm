<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\OwnerAgreementFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

/**
 * The revenue-share model behind every owner statement (Q22).
 *
 * @property int $id
 * @property string|null $reference
 * @property int $yacht_id
 * @property int $owner_client_id
 * @property string $type
 * @property string $revenue_share_model
 * @property numeric $owner_share_pct
 * @property numeric $company_share_pct
 * @property string $statement_frequency
 * @property CarbonImmutable|null $starts_on
 * @property CarbonImmutable|null $ends_on
 * @property bool $auto_renew
 * @property int $notice_days
 * @property int|null $document_id
 * @property array<array-key, mixed>|null $deductible_categories
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read User|null $creator
 * @property-read Document|null $document
 * @property-read Client|null $owner
 * @property-read User|null $updater
 * @property-read Yacht|null $yacht
 *
 * @method static \Database\Factories\OwnerAgreementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereAutoRenew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCompanySharePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDeductibleCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereEndsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereNoticeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereOwnerClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereOwnerSharePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereRevenueShareModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStartsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStatementFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement withoutTrashed()
 *
 * @mixin \Eloquent
 */
class OwnerAgreement extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<OwnerAgreementFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'owner_share_pct' => 'decimal:2',
            'company_share_pct' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'auto_renew' => 'boolean',
            'deductible_categories' => 'array',
        ];
    }

    public function sequenceKey(): string
    {
        return 'owner_agreement';
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'owner_client_id');
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_on === null || $this->ends_on->isFuture());
    }
}
