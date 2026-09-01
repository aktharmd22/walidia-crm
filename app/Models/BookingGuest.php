<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BookingGuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Guest identity data, encrypted at rest and readable only with VIP
 * permission. The manifest and the boarding gate both read this table.
 */
class BookingGuest extends Model implements Auditable
{
    /** @use HasFactory<BookingGuestFactory> */
    use AuditableTrait;

    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['document_number', 'date_of_birth', 'dietary', 'allergies'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['document_number' => 'encrypted', 'date_of_birth' => 'encrypted', 'dietary' => 'encrypted', 'allergies' => 'encrypted', 'is_lead_guest' => 'boolean', 'id_verified' => 'boolean', 'checked_in_at' => 'datetime'];
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
