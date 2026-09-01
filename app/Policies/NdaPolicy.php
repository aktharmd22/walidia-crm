<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Nda;
use App\Models\User;

class NdaPolicy extends ResourcePolicy
{
    protected string $prefix = 'ndas';

    protected ?string $ownerColumn = null;

    public function markSigned(User $user, Nda $nda): bool
    {
        return $user->can('ndas.update') && $nda->signed_at === null;
    }
}
