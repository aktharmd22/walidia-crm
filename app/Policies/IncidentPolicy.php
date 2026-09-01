<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy extends ResourcePolicy
{
    protected string $prefix = 'incidents';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    public function close(User $user, Incident $incident): bool
    {
        return $user->can('incidents.update') && $incident->status !== 'closed';
    }
}
