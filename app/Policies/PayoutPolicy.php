<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy extends ResourcePolicy
{
    protected string $prefix = 'payouts';

    protected ?string $ownerColumn = null;

    public function approve(User $user, Payout $payout): bool
    {
        return $user->can('payouts.approve') && $payout->status === 'pending';
    }

    /** Marking money as gone is a separate act from approving it. */
    public function pay(User $user, Payout $payout): bool
    {
        return $user->can('payouts.pay') && $payout->status === 'approved';
    }
}
