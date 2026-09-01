<?php

declare(strict_types=1);

namespace App\Policies;

class BuyerRequirementPolicy extends ResourcePolicy
{
    protected string $prefix = 'buyer-requirements';

    protected ?string $ownerColumn = 'assigned_user_id';
}
