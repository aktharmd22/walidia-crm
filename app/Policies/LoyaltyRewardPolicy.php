<?php

declare(strict_types=1);

namespace App\Policies;

class LoyaltyRewardPolicy extends ResourcePolicy
{
    protected string $prefix = 'loyalty-rewards';

    /** Automation is configuration: it belongs to the company, not a person. */
    protected ?string $ownerColumn = null;
}
