<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only. Written whenever a VIP field, a guest manifest or a private
 * document is read or exported. Nothing in the application updates or deletes
 * these rows.
 */
class RecordAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'subject_type', 'subject_id', 'field_group',
        'action', 'ip_address', 'user_agent', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
