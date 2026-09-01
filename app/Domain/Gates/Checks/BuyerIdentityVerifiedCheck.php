<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Client;
use App\Models\Offer;
use App\Models\Viewing;
use Illuminate\Database\Eloquent\Model;

/**
 * The buyer has been through KYC. A viewing is where a stranger boards a client's asset.
 */
class BuyerIdentityVerifiedCheck implements GateCheck
{
    public function key(): string
    {
        return 'buyer.identity_verified';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        $client = $this->client($subject);

        return $client !== null && $client->kyc_status === 'verified';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        $client = $this->client($subject);

        return $client === null
            ? 'No buyer is attached to this record.'
            : "{$client->full_name} has not completed KYC verification.";
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        $client = $this->client($subject);

        return $client === null ? null : [
            'label' => 'Open buyer',
            'url' => route('clients.show', $client->getKey()),
        ];
    }

    private function client(Model $subject): ?Client
    {
        if ($subject instanceof Viewing || $subject instanceof Offer) {
            return $subject->client;
        }

        return $subject instanceof Client ? $subject : null;
    }
}
