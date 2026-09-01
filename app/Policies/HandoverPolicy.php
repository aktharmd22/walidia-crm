<?php

declare(strict_types=1);

namespace App\Policies;

class HandoverPolicy extends ResourcePolicy
{
    protected string $prefix = 'handovers';

    protected ?string $ownerColumn = null;
}
