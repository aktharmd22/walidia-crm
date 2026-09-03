<?php

declare(strict_types=1);

namespace App\Policies;

class ClientJourneyPolicy extends ResourcePolicy
{
    protected string $prefix = 'client-journeys';

    /** Automation is configuration: it belongs to the company, not a person. */
    protected ?string $ownerColumn = null;
}
