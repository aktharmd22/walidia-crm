<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OwnerStatement;
use App\Models\User;

class OwnerStatementPolicy extends ResourcePolicy
{
    protected string $prefix = 'owner-statements';

    /** The fleet is worked as a whole; management records are not owned. */
    protected ?string $ownerColumn = null;

    /** Issuing sends the numbers to the owner — a deliberate, separate act. */
    public function issue(User $user, OwnerStatement $statement): bool
    {
        return $user->can('owner-statements.issue') && $statement->status === 'draft';
    }
}
