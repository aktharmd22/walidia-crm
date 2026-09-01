<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SavedViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $module
 * @property string $name
 * @property array<array-key, mixed>|null $filters
 * @property array<array-key, mixed>|null $columns
 * @property bool $is_shared
 * @property bool $is_default
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User|null $user
 *
 * @method static \Database\Factories\SavedViewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereIsShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView withoutTrashed()
 *
 * @mixin \Eloquent
 */
class SavedView extends Model
{
    /** @use HasFactory<SavedViewFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['user_id', 'module', 'name', 'filters', 'columns', 'is_shared', 'is_default'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'is_shared' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
