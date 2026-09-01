<?php

declare(strict_types=1);

namespace App\Policies;

class MarinaPolicy extends ResourcePolicy
{
    protected string $prefix = 'marinas';

    protected ?string $ownerColumn = null;
}
