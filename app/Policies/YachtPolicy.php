<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Yacht;

class YachtPolicy extends ResourcePolicy
{
    protected string $prefix = 'yachts';

    /** The fleet is shared: everyone who can see yachts sees all of them. */
    protected ?string $ownerColumn = null;

    /** Rates are commercial data, not fleet data. */
    public function viewRates(User $user, Yacht $yacht): bool
    {
        return $user->can('finance.view-amounts') || $user->can('charter-proposals.view');
    }

    public function manageAvailability(User $user, Yacht $yacht): bool
    {
        return $user->can('yacht-availability.update');
    }
}
