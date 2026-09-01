<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * AML screening above the threshold.
 *
 * The threshold is a rule parameter rather than a constant, because it is set
 * by regulation and will change without this code changing.
 */
class AmlClearedCheck implements GateCheck
{
    public function key(): string
    {
        return 'aml.cleared';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Transaction) {
            return false;
        }

        $threshold = (float) ($params['threshold'] ?? 0);

        return (float) $subject->agreed_price < $threshold || $subject->aml_cleared;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        $threshold = number_format((float) ($params['threshold'] ?? 0));
        $currency = (string) ($params['currency'] ?? 'AED');

        return "AML screening is required above {$currency} {$threshold} and has not been cleared.";
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
