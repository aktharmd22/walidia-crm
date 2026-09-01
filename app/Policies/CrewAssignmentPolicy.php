<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CrewAssignment;
use App\Models\User;

class CrewAssignmentPolicy extends ResourcePolicy
{
    protected string $prefix = 'crew-assignments';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    /**
     * Dispatch is a guarded transition: the permission decides who may try,
     * the gate decides whether it is possible.
     */
    public function dispatch(User $user, CrewAssignment $assignment): bool
    {
        return $user->can('crew-assignments.dispatch');
    }
}
