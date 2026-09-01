<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Database\Factories\GateRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * One guarded transition, as data (D-004).
 *
 * Editing a rule is audited and versioned, so "who loosened the boarding gate,
 * and when" is answerable — which is the whole reason the rules are data and
 * not conditionals.
 *
 * @property string $key
 * @property string $subject_type
 * @property string $trigger_type
 * @property string|null $trigger_field
 * @property string|null $trigger_to
 * @property string|null $action_key
 * @property string $severity
 * @property string $name_en
 * @property string $block_message_en
 * @property string|null $resolution_label
 * @property list<string>|null $trigger_from
 * @property list<array{check: string, params?: array<string, mixed>, message_en?: string}> $conditions
 * @property array{title?: string, role?: string, due_offset_hours?: int}|null $creates_task
 * @property bool $is_overridable
 * @property bool $is_active
 * @property int $version
 */
class GateRule extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<GateRuleFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'trigger_from' => 'array',
        'conditions' => 'array',
        'creates_task' => 'array',
        'is_overridable' => 'boolean',
        'requires_reason' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // The evaluator caches rules per subject and trigger; changing one has
        // to take effect immediately, not in five minutes.
        static::saved(fn (GateRule $rule) => $rule->forgetCache());
        static::deleted(fn (GateRule $rule) => $rule->forgetCache());

        static::updating(function (GateRule $rule): void {
            if ($rule->isDirty(['conditions', 'severity', 'is_active', 'trigger_to'])) {
                $rule->version = (int) $rule->version + 1;
            }
        });
    }

    public function forgetCache(): void
    {
        foreach (['action', 'transition', 'schedule'] as $trigger) {
            Cache::forget("gates:{$this->subject_type}:{$trigger}");
        }
    }

    /** @return HasMany<GateEvaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(GateEvaluation::class);
    }

    /** @return HasMany<GateOverride, $this> */
    public function overrides(): HasMany
    {
        return $this->hasMany(GateOverride::class);
    }

    public function isHard(): bool
    {
        return $this->severity === 'hard';
    }

    /**
     * @param  Builder<GateRule>  $query
     * @return Builder<GateRule>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_').'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('key', 'like', $like)->orWhere('name_en', 'like', $like);
        });
    }
}
