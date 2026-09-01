<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * An FTA-compliant tax invoice.
 *
 * Once issued it is never edited or deleted: it is voided and credited, because
 * a tax invoice number is a promise to the authority as much as to the client
 * (D-013). The number itself is gapless and never reissued.
 */
class Invoice extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['buyer_trn'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'issued_at' => 'datetime',
            'voided_at' => 'datetime',
            'buyer_trn' => 'encrypted',
        ];
    }

    public function sequenceKey(): string
    {
        return $this->type === 'credit_note' ? 'credit_note' : 'invoice';
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<InvoiceItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /** @return BelongsTo<CostSheet, $this> */
    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class);
    }

    /** @return BelongsTo<Invoice, $this> */
    public function creditNoteOf(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'credit_note_of_id');
    }

    /* ── state ──────────────────────────────────────────────────────────── */

    public function isIssued(): bool
    {
        return $this->issued_at !== null;
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft' && ! $this->isIssued();
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && in_array($this->status, ['issued', 'partially_paid', 'overdue'], true);
    }

    /** Cleared money only: a pending transfer has not paid anything. */
    public function clearedAmount(): float
    {
        return (float) $this->allocations()
            ->whereHas('payment', fn (Builder $query) => $query->where('status', 'cleared'))
            ->sum('amount');
    }

    public function statusTone(): string
    {
        return match (true) {
            $this->status === 'paid' => 'success',
            $this->isOverdue() => 'danger',
            $this->status === 'partially_paid' => 'warning',
            $this->status === 'issued' => 'info',
            in_array($this->status, ['void', 'credited'], true) => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->whereDate('due_date', '<', now());
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
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
