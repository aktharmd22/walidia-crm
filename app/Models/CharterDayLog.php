<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CharterDayLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The log of the day itself.
 *
 * Append-only: a correction is a new entry pointing at the original, because
 * an operations log that can be rewritten is not a log.
 *
 * @property CarbonImmutable $occurred_at
 * @property string $event_type
 */
class CharterDayLog extends Model implements Auditable
{
    /**
     * The charter day, in the order flowchart §13 walks it.
     *
     * The log accepted five types, so eight of the moments the flowchart names
     * — the yacht being set up, the transfer, the service providers arriving,
     * the safety briefing — all had to be filed as "note", which is the same as
     * not recording them: you cannot ask a note when the briefing happened.
     *
     * Boarding, identity checks, extra charges, incidents, damage and the
     * deposit are not here because each is its own record elsewhere, with its
     * own gate. This is the running log around them.
     */
    public const EVENTS = [
        'yacht_arrival',
        'yacht_setup',
        'guest_transfer',
        'provider_arrival',
        'guest_arrival',
        'guest_check_in',
        'safety_briefing',
        'captain_introduction',
        'departure',
        'status_update',
        'fuel',
        'note',
        'arrival',
        'guest_check_out',
    ];

    /** Reads as English on the timeline. */
    public const EVENT_LABELS = [
        'yacht_arrival' => 'Yacht alongside',
        'yacht_setup' => 'Yacht set up',
        'guest_transfer' => 'Guest transfer',
        'provider_arrival' => 'Service provider arrived',
        'guest_arrival' => 'Guests arrived',
        'guest_check_in' => 'Guests checked in',
        'safety_briefing' => 'Safety briefing given',
        'captain_introduction' => 'Captain introduced',
        'departure' => 'Departed',
        'status_update' => 'Status update',
        'fuel' => 'Fuel',
        'note' => 'Note',
        'arrival' => 'Returned alongside',
        'guest_check_out' => 'Guests checked out',
    ];

    use AuditableTrait;

    /** @use HasFactory<CharterDayLogFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'occurred_at' => 'datetime',
        'synced_at' => 'datetime',
        'meta' => 'array',
        'photo_paths' => 'array',
    ];

    /** @return BelongsTo<User, $this> */
    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
