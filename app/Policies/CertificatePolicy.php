<?php

declare(strict_types=1);

namespace App\Policies;

class CertificatePolicy extends ResourcePolicy
{
    protected string $prefix = 'certificates';

    /** The fleet is worked as a whole; management records are not owned. */
    protected ?string $ownerColumn = null;
}
