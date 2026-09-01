<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Record visibility, enforced at the query level rather than in controllers
 * (D-017).
 *
 * The brief requires that a Sales user cannot retrieve another agent's client
 * even by guessing an ID. That has to hold on every path — index, relation,
 * global search, report, a bare find() — so it lives in a global scope, and a
 * record outside the set is simply not there (404, not 403: a 403 confirms the
 * record exists).
 */
class ScopedToOwner implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Console commands, queued jobs and reports run unscoped by design;
        // they call withoutOwnerScope() or run without an authenticated user.
        if (! auth()->hasUser()) {
            return;
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->can('records.view-all')) {
            return;
        }

        $table = $model->getTable();
        $teamIds = $user->can('records.view-team') ? $user->teamMemberIds() : [];

        // Unassigned records are visible to everyone only where the model says
        // so — the Unassigned lead pool needs it; a client record does not.
        $includeUnassigned = property_exists($model, 'ownerScopeIncludesUnassigned')
            && $model->ownerScopeIncludesUnassigned === true;

        $builder->where(function (Builder $query) use ($table, $user, $teamIds, $includeUnassigned): void {
            $query->where("{$table}.assigned_user_id", $user->id);

            if ($includeUnassigned) {
                $query->orWhereNull("{$table}.assigned_user_id");
            }

            if ($teamIds !== []) {
                $query->orWhereIn("{$table}.assigned_user_id", $teamIds);
            }
        });
    }

    /**
     * @param  Builder<Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutOwnerScope', function (Builder $builder): Builder {
            return $builder->withoutGlobalScope(self::class);
        });
    }
}
