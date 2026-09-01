<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

/**
 * The shared shape of every policy in the system.
 *
 * Permission answers "may this role do this verb at all"; the ownership check
 * answers "on this record". Both must pass. Admin is handled by the Gate::before
 * check in AuthServiceProvider, so it is never re-implemented here.
 *
 * Subclasses set $prefix (matching App\Support\Permissions) and override only
 * the abilities whose rules genuinely differ — an issued invoice that cannot be
 * edited, a document that defers to its subject, a VIP field behind its own
 * permission.
 */
abstract class ResourcePolicy
{
    use HandlesAuthorization;

    /** Permission prefix, e.g. 'clients' for clients.view / clients.create. */
    protected string $prefix = '';

    /** Column that carries record ownership, or null if the model is shared. */
    protected ?string $ownerColumn = 'assigned_user_id';

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->prefix}.view");
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can("{$this->prefix}.view") && $this->owns($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->prefix}.create");
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can("{$this->prefix}.update") && $this->owns($user, $model);
    }

    /** "Delete" means archive: a soft delete, reversible from the Archive view (D-008). */
    public function delete(User $user, Model $model): bool
    {
        return $user->can("{$this->prefix}.delete") && $this->owns($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->can("{$this->prefix}.restore") && $this->owns($user, $model);
    }

    /** Nothing is ever hard-deleted from a business table. */
    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $user->can("{$this->prefix}.export");
    }

    public function import(User $user): bool
    {
        return $user->can("{$this->prefix}.import");
    }

    public function reassign(User $user, Model $model): bool
    {
        return $user->can('records.reassign') && $this->owns($user, $model);
    }

    /**
     * Ownership. The global scope already hides records this user may not see,
     * so this is the second line rather than the only one — it matters for
     * models reached through a relation, and for anything loaded unscoped.
     */
    protected function owns(User $user, Model $model): bool
    {
        if ($this->ownerColumn === null || $user->can('records.view-all')) {
            return true;
        }

        $owner = $model->getAttribute($this->ownerColumn);

        if ($owner === null || (int) $owner === $user->id) {
            return true;
        }

        return $user->can('records.view-team') && in_array((int) $owner, $user->teamMemberIds(), true);
    }
}
