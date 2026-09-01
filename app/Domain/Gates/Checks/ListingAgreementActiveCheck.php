<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Model;

/**
 * The central agency agreement has not lapsed. Soft: it warns, it does not stop the work.
 */
class ListingAgreementActiveCheck implements GateCheck
{
    public function key(): string
    {
        return 'listing.agreement_active';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Listing) {
            return true;
        }

        $days = (int) ($params['expiring_days'] ?? 0);

        return $subject->agreementIsActive() && ! $subject->agreementExpiresWithin($days);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        if (! $subject instanceof Listing || $subject->agreement_expires_on === null) {
            return 'The listing agreement is not on file.';
        }

        return $subject->agreementIsActive()
            ? "The listing agreement expires on {$subject->agreement_expires_on->format('d M Y')}."
            : 'The listing agreement has expired.';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Listing ? [
            'label' => 'Open listing',
            'url' => route('brokerage.listings.show', $subject->getKey()),
        ] : null;
    }
}
