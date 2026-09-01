<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Polymorphic subjects are stored as short keys rather than class
         * names, so moving a class between namespaces never orphans a decade
         * of timeline entries, documents and tasks (D-006).
         */
        Relation::enforceMorphMap([
            'user' => Models\User::class,
            'client' => Models\Client::class,
            'client_contact' => Models\ClientContact::class,
            'company' => Models\Company::class,
            'lead' => Models\Lead::class,
            'deal' => Models\Deal::class,
            'task' => Models\Task::class,
            'note' => Models\Note::class,
            'document' => Models\Document::class,
            'yacht' => Models\Yacht::class,
            'yacht_availability_block' => Models\YachtAvailabilityBlock::class,
            'owner_agreement' => Models\OwnerAgreement::class,
            'marina' => Models\Marina::class,
            'charter_enquiry' => Models\CharterEnquiry::class,
            'charter_proposal' => Models\CharterProposal::class,
            'booking' => Models\Booking::class,
            'booking_guest' => Models\BookingGuest::class,
            'cost_sheet' => Models\CostSheet::class,
            'invoice' => Models\Invoice::class,
            'payment' => Models\Payment::class,
            'quotation' => Models\Quotation::class,
            'security_deposit' => Models\SecurityDeposit::class,
            'gate_rule' => Models\GateRule::class,
        ]);

        /*
         * Strict models in development: a lazily loaded relation or a silently
         * discarded attribute becomes an exception here rather than an N+1
         * query or a lost field in production.
         *
         * Missing-attribute access is deliberately NOT strict: a freshly
         * created model legitimately lacks the columns the database defaulted,
         * and turning that into an exception punishes correct code.
         */
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
        Model::preventAccessingMissingAttributes(false);

        Date::use(CarbonImmutable::class);

        Password::defaults(fn () => Password::min((int) config('walidia.password_min_length', 12))
            ->letters()
            ->mixedCase()
            ->numbers()
            ->uncompromised());

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
