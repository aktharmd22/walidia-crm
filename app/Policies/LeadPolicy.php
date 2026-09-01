<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy extends ResourcePolicy
{
    protected string $prefix = 'leads';

    public function assign(User $user, Lead $lead): bool
    {
        return $user->can('leads.update') && ($lead->assigned_user_id === null || $user->can('records.reassign'));
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('leads.update')
            && $user->can('clients.create')
            && $lead->converted_at === null;
    }

    public function merge(User $user, Lead $lead): bool
    {
        return $user->can('leads.update') && $user->can('records.reassign');
    }
}
