<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CharterProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CharterProposalPolicy extends ResourcePolicy
{
    protected string $prefix = 'charter-proposals';

    /** A sent proposal is versioned, never edited. */
    public function update(User $user, Model $model): bool
    {
        return $model instanceof CharterProposal
            && $model->status === 'draft'
            && parent::update($user, $model);
    }

    public function send(User $user, CharterProposal $proposal): bool
    {
        return $user->can('charter-proposals.send')
            && $this->owns($user, $proposal)
            && $proposal->status === 'draft';
    }

    public function accept(User $user, CharterProposal $proposal): bool
    {
        return $user->can('charter-proposals.accept') && $this->owns($user, $proposal);
    }
}
