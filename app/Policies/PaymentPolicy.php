<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentPolicy extends ResourcePolicy
{
    protected string $prefix = 'payments';

    protected ?string $ownerColumn = null;

    /** A cleared, reconciled payment is not edited — it is refunded. */
    public function update(User $user, Model $model): bool
    {
        return $model instanceof Payment
            && $model->reconciled_at === null
            && $user->can('payments.update');
    }

    public function confirmDeposit(User $user, Payment $payment): bool
    {
        return $user->can('payments.confirm-deposit');
    }

    public function reconcile(User $user, Payment $payment): bool
    {
        return $user->can('payments.reconcile');
    }
}
