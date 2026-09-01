<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VendorCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Catering, watersports, transfers, technical — and what each needs on file.
 */
class VendorCategory extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<VendorCategoryFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requires_insurance' => 'boolean',
        'requires_licence' => 'boolean',
    ];

    /** @return HasMany<Vendor, $this> */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
