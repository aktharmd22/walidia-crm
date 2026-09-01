<?php

declare(strict_types=1);

namespace App\Policies;

class CompanyPolicy extends ResourcePolicy
{
    protected string $prefix = 'companies';

    /**
     * A DMC or charter partner is a shared relationship. Hiding it behind one
     * agent's ownership is how the same partner gets entered three times.
     */
    protected ?string $ownerColumn = null;
}
