<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Viewing;

class ViewingPolicy extends ResourcePolicy
{
    protected string $prefix = 'viewings';

    protected ?string $ownerColumn = 'assigned_user_id';

    /** Scheduling is the guarded transition; the gate decides if it is possible. */
    public function schedule(User $user, Viewing $viewing): bool
    {
        return $user->can('viewings.update');
    }
}
