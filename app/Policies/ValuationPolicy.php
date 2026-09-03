<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Valuation;

class ValuationPolicy extends ResourcePolicy
{
    protected string $prefix = 'valuations';

    protected ?string $ownerColumn = null;

    /** Approving the number is the seller's decision, recorded by a broker. */
    public function decide(User $user, Valuation $valuation): bool
    {
        return $user->can('valuations.update') && $valuation->pricing_decision === 'proposed';
    }
}
