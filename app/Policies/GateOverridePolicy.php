<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * The Override Register is read-only for everybody, including Admin. There is
 * no route in this application that edits or deletes a row here, and this
 * policy is the second lock on that.
 */
class GateOverridePolicy extends ResourcePolicy
{
    protected string $prefix = 'gate-overrides';

    protected ?string $ownerColumn = null;

    public function viewAny(User $user): bool
    {
        return $user->can('compliance.view-audit');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can('compliance.view-audit');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }
}
