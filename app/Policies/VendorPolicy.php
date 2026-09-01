<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy extends ResourcePolicy
{
    protected string $prefix = 'vendors';

    /** Operations records are shared: the team works the whole fleet. */
    protected ?string $ownerColumn = null;

    public function approve(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.update') && ! $vendor->is_approved;
    }
}
