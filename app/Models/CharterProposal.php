<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasOwnerScope;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use App\Models\Concerns\TracksBlame;
use App\Models\Scopes\ScopedToOwner;
use Database\Factories\CharterProposalFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A priced offer to the client.
 *
 * Versioned rather than mutated: a new version supersedes the old one, so what
 * the client actually saw and accepted is still on file afterwards.
 */
#[ScopedBy([ScopedToOwner::class])]
class CharterProposal extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CharterProposalFactory> */
    use HasFactory;

    /** @use HasOwnerScope<CharterProposal> */
    use HasOwnerScope;

    use HasReference;
    use HasTimeline;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function sequenceKey(): string
    {
        return 'proposal';
    }

    /** @return BelongsTo<CharterEnquiry, $this> */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(CharterEnquiry::class, 'charter_enquiry_id');
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<ProposalItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class)->orderBy('sort_order');
    }

    /** @return BelongsTo<Document, $this> */
    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'pdf_document_id');
    }

    public function isAcceptable(): bool
    {
        return in_array($this->status, ['sent', 'viewed'], true)
            && ($this->valid_until === null || ! $this->valid_until->isPast());
    }

    public function hasExpired(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->isPast()
            && ! in_array($this->status, ['accepted', 'declined'], true);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'accepted' => 'success',
            'sent', 'viewed' => 'info',
            'declined' => 'danger',
            'expired' => 'neutral',
            default => 'warning',
        };
    }

    /**
     * @param  Builder<CharterProposal>  $query
     * @return Builder<CharterProposal>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('reference', 'like', $like)
                ->orWhereHas('client', fn (Builder $client) => $client->where('full_name', 'like', $like));
        });
    }
}
