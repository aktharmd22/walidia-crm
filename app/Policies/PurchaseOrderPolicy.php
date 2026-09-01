<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy extends ResourcePolicy
{
    protected string $prefix = 'purchase-orders';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    public function approve(User $user, PurchaseOrder $order): bool
    {
        return $user->can('purchase-orders.approve') && $order->status === 'pending_approval';
    }
}
