<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * How a vendor actually performed on a charter.
 */
class VendorRating extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<VendorRatingFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
