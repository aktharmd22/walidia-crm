<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DamageReport;
use App\Models\User;

class DamageReportPolicy extends ResourcePolicy
{
    protected string $prefix = 'damage-reports';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    /** Closing the inspection is what unlocks the deposit release. */
    public function close(User $user, DamageReport $report): bool
    {
        return $user->can('damage-reports.close') && ! $report->isClosed();
    }
}
