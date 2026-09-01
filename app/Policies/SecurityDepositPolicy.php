<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SecurityDeposit;
use App\Models\User;

class SecurityDepositPolicy extends ResourcePolicy
{
    protected string $prefix = 'security-deposits';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    public function release(User $user, SecurityDeposit $deposit): bool
    {
        return $user->can('security-deposits.release') && $deposit->isHeld();
    }

    public function collect(User $user, SecurityDeposit $deposit): bool
    {
        return $user->can('security-deposits.update');
    }
}
