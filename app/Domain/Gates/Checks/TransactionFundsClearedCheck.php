<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * The balance has arrived and been reconciled — not merely promised.
 */
class TransactionFundsClearedCheck implements GateCheck
{
    public function key(): string
    {
        return 'transaction.funds_cleared';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        return $subject instanceof Transaction && $subject->fundsCleared();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        return 'The final payment has not cleared and been reconciled.';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Transaction ? [
            'label' => 'Open transaction',
            'url' => route('brokerage.transactions.show', $subject->getKey()),
        ] : null;
    }
}
