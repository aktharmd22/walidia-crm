<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\CharterEnquiry;
use Illuminate\Database\Eloquent\Model;

/**
 * Availability is only locked once the client has actually accepted a
 * proposal — a verbal yes is not a proposal.
 */
class ProposalAcceptedCheck implements GateCheck
{
    public function key(): string
    {
        return 'proposal.accepted';
    }

    public function passes(Model $subject, array $params): bool
    {
        $enquiry = $subject instanceof Booking ? $subject->enquiry : $subject;

        if (! $enquiry instanceof CharterEnquiry) {
            return false;
        }

        return $enquiry->proposals()
            ->where('status', 'accepted')
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()))
            ->exists();
    }

    public function failureMessage(Model $subject, array $params): string
    {
        return 'No accepted proposal is on file, or the accepted one has expired.';
    }

    public function resolution(Model $subject, array $params): ?array
    {
        $enquiry = $subject instanceof Booking ? $subject->enquiry : $subject;

        return $enquiry === null ? null : [
            'label' => 'Open proposals',
            'url' => route('charter.enquiries.show', $enquiry->getKey()),
        ];
    }
}
