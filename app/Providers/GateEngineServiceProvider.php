<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Gates\Checks;
use App\Domain\Gates\GateCheck;
use App\Domain\Gates\GateCheckRegistry;
use App\Domain\Gates\GateEvaluator;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the gate engine.
 *
 * Checks are registered here, in one list, so the rule editor can offer exactly
 * what exists — and so a rule referring to a check nobody implemented fails
 * loudly instead of passing silently.
 */
class GateEngineServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /** @var list<class-string<GateCheck>> */
    public const CHECKS = [
        Checks\ProposalAcceptedCheck::class,
        Checks\KycVerifiedCheck::class,
        Checks\DepositClearedCheck::class,
        Checks\OperationalReleaseCheck::class,
        Checks\ItineraryPresentCheck::class,
        Checks\ManifestCompleteCheck::class,
        Checks\GuestsIdentityVerifiedCheck::class,
        Checks\SafetyBriefingLoggedCheck::class,
        Checks\ReceiptsGeneratedCheck::class,
        Checks\CrewDocumentsValidCheck::class,
        Checks\DamageInspectionClosedCheck::class,
        Checks\PayoutsIssuedCheck::class,
        Checks\NdaSignedCheck::class,
        Checks\BuyerIdentityVerifiedCheck::class,
        Checks\ProofOfFundsCheck::class,
        Checks\TransactionFundsClearedCheck::class,
        Checks\AmlClearedCheck::class,
        Checks\ListingAgreementActiveCheck::class,
    ];

    public function register(): void
    {
        $this->app->singleton(GateCheckRegistry::class, function (): GateCheckRegistry {
            $registry = new GateCheckRegistry;

            foreach (self::CHECKS as $check) {
                $registry->register($this->app->make($check));
            }

            return $registry;
        });

        $this->app->singleton(GateEvaluator::class);
    }

    /**
     * @return list<string>
     */
    public function provides(): array
    {
        return [GateCheckRegistry::class, GateEvaluator::class];
    }
}
