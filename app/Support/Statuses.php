<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The status vocabularies, in one place.
 *
 * These are taken from the company's own flowcharts rather than invented, so
 * the word on the screen is the word the team says out loud. That matters more
 * than it looks: "Option Held" and "Tentative" are different commitments to a
 * client, and a system that collapses them into "draft" quietly loses the
 * distinction the business runs on.
 *
 * Each list is ordered as the work actually flows, so a select box built from
 * one reads as a pipeline rather than an alphabet.
 */
class Statuses
{
    /**
     * Charter enquiry — flowchart §2, "Inquiry Status".
     *
     * @var array<string, string>
     */
    public const ENQUIRY = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'matching' => 'Matching',
        'proposed' => 'Proposal sent',
        'waiting_client' => 'Waiting on client',
        'follow_up' => 'Follow-up',
        'qualified' => 'Qualified',
        'won' => 'Won',
        'lost' => 'Lost',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Proposal — flowchart §4, "Proposal Status".
     *
     * @var array<string, string>
     */
    public const PROPOSAL = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'viewed' => 'Viewed',
        'revision_requested' => 'Revision requested',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'expired' => 'Expired',
    ];

    /**
     * Booking — flowchart §5, "Booking Status (Dashboard) Per Charter".
     *
     * `option_held` is a soft hold on the yacht that expires; `tentative` is a
     * client who has said yes without paperwork. Both lock availability, and
     * both are how a yacht gets double-sold if the system cannot express them.
     *
     * @var array<string, string>
     */
    public const BOOKING = [
        'draft' => 'Draft',
        'tentative' => 'Tentative',
        'option_held' => 'Option held',
        'pending_contract' => 'Awaiting documents',
        'contract_signed' => 'Contract signed',
        'deposit_pending' => 'Awaiting payment',
        'deposit_paid' => 'Deposit paid',
        'confirmed' => 'Confirmed',
        'upcoming' => 'Upcoming charter',
        'in_progress' => 'Operation in progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No show',
    ];

    /**
     * Experience type — flowchart §2, "Experience Selection".
     *
     * @var array<string, string>
     */
    public const EXPERIENCE = [
        'leisure' => 'Leisure',
        'day_charter' => 'Day charter',
        'sunset_cruise' => 'Sunset cruise',
        'birthday' => 'Birthday',
        'wedding' => 'Wedding',
        'engagement' => 'Proposal or engagement',
        'corporate' => 'Corporate event',
        'government' => 'Government event',
        'fishing' => 'Fishing',
        'overnight' => 'Overnight',
        'multi_day' => 'Multi-day charter',
        'photoshoot' => 'Photoshoot or filming',
    ];

    /**
     * Who the client is — flowchart §1, "Lead Type".
     *
     * @var array<string, string>
     */
    public const CLIENT_TYPE = [
        'individual' => 'Individual',
        'corporate' => 'Corporate',
        'government' => 'Government',
        'dmc' => 'DMC',
        'concierge' => 'Concierge',
        'charter_partner' => 'Charter partner',
        'broker' => 'Broker',
        'supplier' => 'Supplier',
    ];

    /**
     * The keys of a vocabulary, for a validation rule.
     *
     * @param  array<string, string>  $vocabulary
     * @return list<string>
     */
    public static function keys(array $vocabulary): array
    {
        return array_keys($vocabulary);
    }

    /**
     * A vocabulary as select options.
     *
     * @param  array<string, string>  $vocabulary
     * @return list<array{value: string, label: string}>
     */
    public static function options(array $vocabulary): array
    {
        $options = [];

        foreach ($vocabulary as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
