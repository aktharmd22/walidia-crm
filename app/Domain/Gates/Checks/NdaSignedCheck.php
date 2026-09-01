<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Listing;
use App\Models\Nda;
use App\Models\Viewing;
use Illuminate\Database\Eloquent\Model;

/**
 * A signed, unexpired NDA for this buyer on this listing.
 *
 * Sellers at this end of the market do not want the sale known. Showing the
 * yacht before the NDA is signed is the single most expensive courtesy a
 * broker can extend.
 */
class NdaSignedCheck implements GateCheck
{
    public function key(): string
    {
        return 'nda.signed';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        if (! $subject instanceof Viewing) {
            return false;
        }

        if ($subject->listing?->requires_nda === false) {
            return true;
        }

        return Nda::query()
            ->where('client_id', $subject->client_id)
            ->where(fn ($query) => $query->where('listing_id', $subject->listing_id)->orWhere('scope', 'fleet'))
            ->whereNotNull('signed_at')
            ->where(fn ($query) => $query->whereNull('expires_on')->orWhereDate('expires_on', '>=', now()))
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        return 'No signed NDA is on file for this buyer and listing.';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        return $subject instanceof Viewing ? [
            'label' => 'Open NDAs',
            'url' => route('brokerage.ndas.index', ['client_id' => $subject->client_id]),
        ] : null;
    }
}
