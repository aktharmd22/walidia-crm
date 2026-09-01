<?php

declare(strict_types=1);

namespace App\Policies;

class GateRulePolicy extends ResourcePolicy
{
    protected string $prefix = 'gate-rules';

    protected ?string $ownerColumn = null;
}
