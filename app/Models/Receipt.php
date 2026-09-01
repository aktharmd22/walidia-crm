<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use Database\Factories\ReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Issued against a cleared payment. A deal cannot close without one for every payment.
 */
class Receipt extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ReceiptFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function sequenceKey(): string
    {
        return 'receipt';
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
