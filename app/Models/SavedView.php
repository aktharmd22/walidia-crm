<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SavedViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
