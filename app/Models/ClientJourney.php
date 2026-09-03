<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use Database\Factories\ClientJourneyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What happens after the money is settled: thank you, feedback,
 * review, survey, complaint, follow-ups, and the next conversation.
 *
 * Both flowcharts end here, and it is the part most systems leave to a
 * spreadsheet — which is why repeat business is where it leaks.
 */
class ClientJourney extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ClientJourneyFactory> */
    use HasFactory;

    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'thank_you_sent_at' => 'datetime',
        'feedback_requested_at' => 'datetime',
        'review_requested_at' => 'datetime',
        'survey_sent_at' => 'datetime',
        'complaint_raised' => 'boolean',
        'complaint_resolved_at' => 'datetime',
        'follow_ups_sent' => 'array',
        'upsell_interests' => 'array',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** Which of the scheduled follow-ups have gone out. */
    public function followUpSent(int $days): bool
    {
        return array_key_exists((string) $days, $this->follow_ups_sent ?? []);
    }

    public function recordFollowUp(int $days): void
    {
        $sent = $this->follow_ups_sent ?? [];
        $sent[(string) $days] = now()->toIso8601String();

        $this->forceFill(['follow_ups_sent' => $sent])->save();
    }

    public function hasOpenComplaint(): bool
    {
        return $this->complaint_raised && $this->complaint_resolved_at === null;
    }
}
