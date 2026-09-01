<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy extends ResourcePolicy
{
    protected string $prefix = 'transactions';

    protected ?string $ownerColumn = null;

    /** The transfer itself — money cleared, AML clear, and only then. */
    public function transferOwnership(User $user, Transaction $transaction): bool
    {
        return $user->can('transactions.transfer-ownership') && ! $transaction->isTransferred();
    }

    public function clearAml(User $user, Transaction $transaction): bool
    {
        return $user->can('transactions.clear-aml');
    }
}
