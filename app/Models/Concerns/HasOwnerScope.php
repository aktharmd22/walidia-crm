<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\ScopedToOwner;
use Illuminate\Database\Eloquent\Builder;

/**
 * Companion to the ScopedToOwner global scope (D-017).
 *
 * Escaping the scope is deliberately explicit and greppable: reports, console
 * commands and duplicate checks say so in the query, so an audit can find
 * every place visibility is bypassed.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
trait HasOwnerScope
{
    /**
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeWithoutOwnerScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ScopedToOwner::class);
    }
}
