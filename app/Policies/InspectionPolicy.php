<?php

declare(strict_types=1);

namespace App\Policies;

class InspectionPolicy extends ResourcePolicy
{
    protected string $prefix = 'inspections';

    protected ?string $ownerColumn = null;
}
