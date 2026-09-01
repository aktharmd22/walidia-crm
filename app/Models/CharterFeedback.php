<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CharterFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Post-charter follow-up: how it actually went, in the client's words.
 */
class CharterFeedback extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CharterFeedbackFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'charter_feedback';

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'ratings' => 'array',
    ];

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
