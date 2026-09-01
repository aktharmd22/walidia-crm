<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceJob;
use App\Models\User;

class MaintenanceJobPolicy extends ResourcePolicy
{
    protected string $prefix = 'maintenance';

    /** The fleet is worked as a whole; management records are not owned. */
    protected ?string $ownerColumn = null;

    public function complete(User $user, MaintenanceJob $job): bool
    {
        return $user->can('maintenance.update') && $job->completed_at === null;
    }
}
