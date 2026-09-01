<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\NdaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The non-disclosure agreement a buyer signs before seeing anything.
 *
 * UHNW sellers do not want their yacht's sale known; a signed NDA is the hard
 * gate in front of every viewing.
 */
class Nda extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<NdaFactory> */
    use HasFactory;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'signed_at' => 'datetime',
        'expires_on' => 'date',
    ];

    public function sequenceKey(): string
    {
        return 'nda';
    }

    /** @return BelongsTo<Listing, $this> */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null
            && ($this->expires_on === null || ! $this->expires_on->isPast());
    }
}
