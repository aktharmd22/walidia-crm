<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy extends ResourcePolicy
{
    protected string $prefix = 'offers';

    protected ?string $ownerColumn = 'assigned_user_id';

    public function submit(User $user, Offer $offer): bool
    {
        return $user->can('offers.submit') && $offer->status === 'draft';
    }

    public function respond(User $user, Offer $offer): bool
    {
        return $user->can('offers.update') && in_array($offer->status, ['submitted', 'countered'], true);
    }
}
