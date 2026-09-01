<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityPolicy extends ResourcePolicy
{
    protected string $prefix = 'activities';

    protected ?string $ownerColumn = null;

    /** Author edits for 24 hours, then it is history. The edit is audited either way. */
    public function update(User $user, Model $model): bool
    {
        return $model instanceof Activity
            && $user->can('activities.update')
            && $model->isEditableBy($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }
}
