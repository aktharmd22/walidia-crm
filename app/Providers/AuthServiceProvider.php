<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models;
use App\Policies;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policies are registered explicitly rather than auto-discovered, so a
     * model without a policy fails loudly instead of falling through to a
     * permissive default.
     *
     * @var array<class-string, class-string>
     */
    public array $policies = [
        Models\Client::class => Policies\ClientPolicy::class,
        Models\Company::class => Policies\CompanyPolicy::class,
        Models\Lead::class => Policies\LeadPolicy::class,
        Models\Deal::class => Policies\DealPolicy::class,
        Models\Task::class => Policies\TaskPolicy::class,
        Models\Document::class => Policies\DocumentPolicy::class,
        Models\Yacht::class => Policies\YachtPolicy::class,
        Models\Marina::class => Policies\MarinaPolicy::class,
        Models\Activity::class => Policies\ActivityPolicy::class,
        Models\CharterEnquiry::class => Policies\CharterEnquiryPolicy::class,
        Models\CharterProposal::class => Policies\CharterProposalPolicy::class,
        Models\Booking::class => Policies\BookingPolicy::class,
        Models\CostSheet::class => Policies\CostSheetPolicy::class,
        Models\Invoice::class => Policies\InvoicePolicy::class,
        Models\Payment::class => Policies\PaymentPolicy::class,
        Models\GateRule::class => Policies\GateRulePolicy::class,
        Models\GateOverride::class => Policies\GateOverridePolicy::class,
        Models\Crew::class => Policies\CrewPolicy::class,
        Models\CrewAssignment::class => Policies\CrewAssignmentPolicy::class,
        Models\CrewPayout::class => Policies\CrewPayoutPolicy::class,
        Models\Vendor::class => Policies\VendorPolicy::class,
        Models\PurchaseOrder::class => Policies\PurchaseOrderPolicy::class,
        Models\Incident::class => Policies\IncidentPolicy::class,
        Models\DamageReport::class => Policies\DamageReportPolicy::class,
        Models\SecurityDeposit::class => Policies\SecurityDepositPolicy::class,
        Models\Listing::class => Policies\ListingPolicy::class,
        Models\BuyerRequirement::class => Policies\BuyerRequirementPolicy::class,
        Models\Nda::class => Policies\NdaPolicy::class,
        Models\Viewing::class => Policies\ViewingPolicy::class,
        Models\Offer::class => Policies\OfferPolicy::class,
        Models\Survey::class => Policies\SurveyPolicy::class,
        Models\Transaction::class => Policies\TransactionPolicy::class,
        Models\Handover::class => Policies\HandoverPolicy::class,
        Models\ManagementAgreement::class => Policies\ManagementAgreementPolicy::class,
        Models\Certificate::class => Policies\CertificatePolicy::class,
        Models\MaintenanceJob::class => Policies\MaintenanceJobPolicy::class,
        Models\OwnerStatement::class => Policies\OwnerStatementPolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        /*
         * Admin holds everything. Granting it through a before-check rather
         * than a stored permission list means a permission added in a later
         * phase is never silently missing from the Admin role.
         *
         * It deliberately does NOT bypass gate rules: a hard gate still blocks
         * an Admin until they record an override with a reason (D-004).
         */
        Gate::before(function (Models\User $user, string $ability): ?bool {
            return $user->hasRole(Roles::ADMIN) ? true : null;
        });
    }
}
