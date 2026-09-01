<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Suppliers to a charter. Bank details are encrypted; ratings are per booking.
 *
 * @property string $legal_name
 * @property string|null $trade_name
 * @property string $status
 * @property bool $is_approved
 */
class Vendor extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['trn', 'bank_details'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'trn' => 'encrypted',
        'bank_details' => 'encrypted',
        'licence_expiry' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'rating_avg' => 'decimal:2',
    ];

    public function sequenceKey(): string
    {
        return 'vendor';
    }

    /** @return BelongsTo<VendorCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    /** @return HasMany<VendorRating, $this> */
    public function ratings(): HasMany
    {
        return $this->hasMany(VendorRating::class);
    }

    /** @return HasMany<PurchaseOrder, $this> */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }

    /**
     * @param  Builder<Vendor>  $query
     * @return Builder<Vendor>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('legal_name', 'like', $like)
                ->orWhere('trade_name', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
