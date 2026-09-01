<?php

declare(strict_types=1);

namespace App\Policies;

class ManagementAgreementPolicy extends ResourcePolicy
{
    protected string $prefix = 'management-agreements';

    /** The fleet is worked as a whole; management records are not owned. */
    protected ?string $ownerColumn = null;
}
