<?php

declare(strict_types=1);

namespace App\Policies;

class CrewPayoutPolicy extends ResourcePolicy
{
    protected string $prefix = 'crew-payouts';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;
}
