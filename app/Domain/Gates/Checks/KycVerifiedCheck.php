<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

/**
 * No contract is generated for a client whose identity has not been
 * verified — this is the field the compliance queue writes.
 */
class KycVerifiedCheck implements GateCheck
{
    public function key(): string
    {
        return 'kyc.verified';
    }

    public function passes(Model $subject, array $params): bool
    {
        $client = $this->clientFor($subject);

        if ($client === null) {
            return false;
        }

        if ($client->kyc_status !== 'verified') {
            return false;
        }

        // Verification that has lapsed is not verification.
        return $client->kyc_expires_on === null || $client->kyc_expires_on->isFuture();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        $client = $this->clientFor($subject);

        return match (true) {
            $client === null => 'No client is attached to this record.',
            $client->kyc_status === 'not_started' => 'KYC has not been started for this client.',
            $client->kyc_status === 'pending' => 'KYC documents are uploaded but not yet verified.',
            $client->kyc_status === 'rejected' => 'KYC was rejected for this client.',
            default => 'The client KYC verification has expired.',
        };
    }

    public function resolution(Model $subject, array $params): ?array
    {
        $client = $this->clientFor($subject);

        return $client === null ? null : [
            'label' => 'Open client KYC',
            'url' => route('clients.show', $client->getKey()),
        ];
    }

    private function clientFor(Model $subject): ?Client
    {
        if ($subject instanceof Client) {
            return $subject;
        }

        $client = method_exists($subject, 'client') ? $subject->getAttribute('client') : null;

        return $client instanceof Client ? $client : null;
    }
}
