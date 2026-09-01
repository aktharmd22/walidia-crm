<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy extends ResourcePolicy
{
    protected string $prefix = 'listings';

    protected ?string $ownerColumn = 'assigned_user_id';

    public function publish(User $user, Listing $listing): bool
    {
        return $user->can('listings.publish');
    }
}
