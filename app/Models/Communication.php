<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\CommunicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Everything the company has ever sent a client.
 *
 * "Did you tell me?" is a question a charter business gets asked, and the
 * answer has to be a record rather than someone's memory.
 */
class Communication extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CommunicationFactory> */
    use HasFactory;

    use HasReference;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function sequenceKey(): string
    {
        return 'communication';
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<MessageTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'delivered', 'read' => 'success',
            'sent' => 'info',
            'failed' => 'danger',
            default => 'neutral',
        };
    }
}
