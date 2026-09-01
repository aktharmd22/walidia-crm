<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ListOptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Settings → Lists. Every dropdown the business wants to change without a
 * deployment: experience types, incident categories, cabin types, and so on.
 *
 * @property int $id
 * @property string $list_key
 * @property string $value
 * @property string $label_en
 * @property string|null $label_ar
 * @property string|null $colour_token
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_system
 * @property array<array-key, mixed>|null $meta
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 *
 * @method static \Database\Factories\ListOptionFactory factory($count = null, $state = [])
 * @method static Builder<static>|ListOption list(string $key)
 * @method static Builder<static>|ListOption newModelQuery()
 * @method static Builder<static>|ListOption newQuery()
 * @method static Builder<static>|ListOption onlyTrashed()
 * @method static Builder<static>|ListOption query()
 * @method static Builder<static>|ListOption whereColourToken($value)
 * @method static Builder<static>|ListOption whereCreatedAt($value)
 * @method static Builder<static>|ListOption whereDeletedAt($value)
 * @method static Builder<static>|ListOption whereId($value)
 * @method static Builder<static>|ListOption whereIsActive($value)
 * @method static Builder<static>|ListOption whereIsSystem($value)
 * @method static Builder<static>|ListOption whereLabelAr($value)
 * @method static Builder<static>|ListOption whereLabelEn($value)
 * @method static Builder<static>|ListOption whereListKey($value)
 * @method static Builder<static>|ListOption whereMeta($value)
 * @method static Builder<static>|ListOption whereSortOrder($value)
 * @method static Builder<static>|ListOption whereUpdatedAt($value)
 * @method static Builder<static>|ListOption whereValue($value)
 * @method static Builder<static>|ListOption withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|ListOption withoutTrashed()
 *
 * @mixin \Eloquent
 */
class ListOption extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ListOptionFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'is_active' => 'boolean', 'is_system' => 'boolean'];
    }

    /**
     * @param  Builder<ListOption>  $query
     * @return Builder<ListOption>
     */
    public function scopeList(Builder $query, string $key): Builder
    {
        return $query->where('list_key', $key)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Options for one list, shaped for a <Select>.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public static function options(string $key): Collection
    {
        return static::list($key)->get()->map(fn (ListOption $option): array => [
            'value' => $option->value,
            'label' => app()->getLocale() === 'ar' && $option->label_ar
                ? $option->label_ar
                : $option->label_en,
        ]);
    }
}
