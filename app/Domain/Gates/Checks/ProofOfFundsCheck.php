<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Model;

/**
 * Proof of funds, where the mandate demands it.
 *
 * A seller taking their yacht off the market for an offer is entitled to know
 * the buyer can complete.
 */
class ProofOfFundsCheck implements GateCheck
{
    public function key(): string
    {
        return 'offer.proof_of_funds';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Offer) {
            return false;
        }

        return $subject->listing?->requires_proof_of_funds === false
            || $subject->proof_of_funds_received;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        return 'This listing requires proof of funds, and none is recorded for the buyer.';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Offer ? [
            'label' => 'Open offer',
            'url' => route('brokerage.offers.show', $subject->getKey()),
        ] : null;
    }
}
