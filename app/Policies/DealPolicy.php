<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy extends ResourcePolicy
{
    protected string $prefix = 'deals';

    /**
     * Moving a card is not the same as editing the record behind it: the move
     * is what the gate engine guards, so it is its own ability.
     */
    public function moveStage(User $user, Deal $deal): bool
    {
        return $user->can('deals.update') && $this->owns($user, $deal) && $deal->isOpen();
    }
}
