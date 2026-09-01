<?php

declare(strict_types=1);

namespace App\Policies;

class CrewPolicy extends ResourcePolicy
{
    protected string $prefix = 'crew';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;
}
