<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasReference;
use App\Models\Concerns\TracksBlame;
use Database\Factories\CrewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Captains, engineers, deckhands, stewards and chefs.
 *
 * Identity data is encrypted like every other person record, and document
 * expiry is what the dispatch gate reads.
 *
 * @property string $full_name
 * @property string $role
 * @property string $status
 * @property numeric|null $day_rate
 */
class Crew extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<CrewFactory> */
    use HasFactory;

    use HasReference;
    use SoftDeletes;
    use TracksBlame;

    protected $table = 'crew';

    protected $guarded = ['id'];

    /** @var list<string> */
    protected array $auditExclude = ['passport_number', 'emirates_id', 'date_of_birth', 'bank_details'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'passport_number' => 'encrypted',
        'emirates_id' => 'encrypted',
        'date_of_birth' => 'encrypted',
        'bank_details' => 'encrypted',
        'day_rate' => 'decimal:2',
    ];

    public function sequenceKey(): string
    {
        return 'crew';
    }

    /** @return HasMany<CrewDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(CrewDocument::class, 'crew_id');
    }

    /** @return HasMany<CrewAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(CrewAssignment::class, 'crew_id');
    }

    /** @return HasMany<CrewPayout, $this> */
    public function payouts(): HasMany
    {
        return $this->hasMany(CrewPayout::class, 'crew_id');
    }

    protected static function booted(): void
    {
        static::saving(function (Crew $crew): void {
            $crew->full_name = trim(implode(' ', array_filter([$crew->first_name, $crew->last_name])));
        });
    }

    /** Any document already expired — a hard stop on dispatch. */
    public function hasExpiredDocuments(): bool
    {
        return $this->documents()
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<', now())
            ->exists();
    }

    /** Anything lapsing inside the window — a warning, not a stop. */
    public function documentsExpiringWithin(int $days): int
    {
        return $this->documents()
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '>=', now())
            ->whereDate('expires_on', '<=', now()->addDays($days))
            ->count();
    }

    /**
     * @param  Builder<Crew>  $query
     * @return Builder<Crew>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('full_name', 'like', $like)
                ->orWhere('role', 'like', $like)
                ->orWhere('reference', 'like', $like);
        });
    }
}
