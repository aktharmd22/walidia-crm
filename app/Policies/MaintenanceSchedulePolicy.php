<?php

declare(strict_types=1);

namespace App\Policies;

class MaintenanceSchedulePolicy extends ResourcePolicy
{
    protected string $prefix = 'maintenance-schedules';

    protected ?string $ownerColumn = null;
}
