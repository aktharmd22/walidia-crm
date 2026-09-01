<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\SignatureRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $document_id
 * @property string $provider
 * @property string|null $provider_ref
 * @property int|null $signer_client_id
 * @property string $signer_name
 * @property string $signer_email
 * @property CarbonImmutable|null $sent_at
 * @property CarbonImmutable|null $viewed_at
 * @property CarbonImmutable|null $signed_at
 * @property CarbonImmutable|null $declined_at
 * @property string|null $decline_reason
 * @property string|null $ip_address
 * @property array<array-key, mixed>|null $audit_trail
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User|null $creator
 * @property-read Document|null $document
 * @property-read Client|null $signer
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\SignatureRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeclineReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereProviderRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest withoutTrashed()
 *
 * @mixin \Eloquent
 */
class SignatureRequest extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<SignatureRequestFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'audit_trail' => 'array',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'signed_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'signer_client_id');
    }
}
