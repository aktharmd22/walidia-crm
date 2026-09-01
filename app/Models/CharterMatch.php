<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CharterMatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A scored yacht suggestion. The reasons are stored so a broker can defend the shortlist to a client.
 *
 * @property int $score
 * @property list<array{factor: string, detail: string, weight: int}>|null $reasons
 * @property bool $is_shortlisted
 * @property-read Yacht|null $yacht
 */
class CharterMatch extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CharterMatchFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'reasons' => 'array',
        'is_shortlisted' => 'boolean',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /** @return BelongsTo<CharterEnquiry, $this> */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(CharterEnquiry::class, 'charter_enquiry_id');
    }

    /** @return BelongsTo<Yacht, $this> */
    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
