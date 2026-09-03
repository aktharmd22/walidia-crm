<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The parts of the two flowcharts that had no home yet.
 *
 * Each of these was a step the business already performs — a yacht gets
 * inspected before it is listed, a seller gets paid after completion, a buyer
 * signs an acceptance form at handover — that the system was asking someone to
 * remember rather than record.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * Brokerage §2: the valuation behind an asking price.
         *
         * A listing price that nobody can defend is how a mandate is lost at
         * the first offer, so the comparables and the broker's own figure are
         * kept beside the number that went on the listing.
         */
        Schema::create('valuations', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('valued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('valued_on');
            $table->decimal('market_low', 15, 2)->nullable();
            $table->decimal('market_high', 15, 2)->nullable();
            $table->decimal('broker_valuation', 15, 2);
            $table->decimal('recommended_asking', 15, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('comparables')->nullable();
            $table->text('rationale')->nullable();
            // proposed·approved·adjusted — the seller's decision on the number.
            $table->string('pricing_decision', 24)->default('proposed');
            $table->decimal('agreed_asking', 15, 2)->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        /*
         * Brokerage §2 and §9: the two inspections either end of a sale — the
         * one that decides whether we list it, and the one before delivery.
         */
        Schema::create('inspections', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('handover_id')->nullable()->constrained()->nullOnDelete();
            // listing·pre_delivery — same act, different moment.
            $table->string('type', 24)->default('listing');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('inspected_at')->nullable();
            $table->unsignedTinyInteger('hull_condition')->nullable();
            $table->unsignedTinyInteger('engine_condition')->nullable();
            $table->unsignedTinyInteger('interior_condition')->nullable();
            $table->unsignedTinyInteger('systems_condition')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommended_works')->nullable();
            $table->decimal('estimated_works_cost', 15, 2)->nullable();
            $table->json('photo_paths')->nullable();
            $table->string('outcome', 24)->nullable(); // clear·defects·failed
            $table->string('status', 24)->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'type']);
        });

        /*
         * Brokerage §8: money leaving the company.
         *
         * The buyer's payment was already recorded; what the seller, the
         * co-broker and the referrer are owed had nowhere to live, which is
         * precisely the half of a deal that gets forgotten.
         */
        Schema::create('payouts', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('transaction_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            // seller·co_broker·referral·vendor·crew — who is being paid, and why.
            $table->string('type', 24);
            $table->string('payee_name', 190);
            $table->foreignId('payee_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('payee_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('payee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('AED');
            $table->decimal('amount_aed', 15, 2)->nullable();
            $table->string('method', 24)->default('bank_transfer');
            $table->string('bank_reference', 90)->nullable();
            $table->date('due_on')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            // pending·approved·paid·cancelled
            $table->string('status', 24)->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'due_on']);
        });

        /* Charter §18: preventive maintenance that recurs, not a one-off job. */
        Schema::create('maintenance_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->string('system', 48);   // engines·generator·air_conditioning·electrical·plumbing·hull·cleaning
            $table->string('title', 190);
            $table->text('description')->nullable();
            // Whichever comes first: the calendar or the engine hours.
            $table->unsignedSmallInteger('interval_days')->nullable();
            $table->unsignedInteger('interval_engine_hours')->nullable();
            $table->date('last_done_on')->nullable();
            $table->unsignedInteger('last_done_engine_hours')->nullable();
            $table->date('next_due_on')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('budget_cost', 15, 2)->nullable();
            $table->boolean('blocks_charter')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'next_due_on']);
        });

        /* Brokerage §9: the paperwork that ends a sale. */
        Schema::table('handovers', function (Blueprint $table): void {
            $table->boolean('pre_delivery_inspection_done')->default(false)->after('scheduled_at');
            $table->boolean('documentation_package_prepared')->default(false)->after('pre_delivery_inspection_done');
            $table->boolean('crew_briefing_acknowledged')->default(false)->after('documentation_package_prepared');
            $table->boolean('client_acceptance_signed')->default(false)->after('crew_briefing_acknowledged');
            $table->boolean('delivery_certificate_signed')->default(false)->after('client_acceptance_signed');
            $table->string('delivery_certificate_path')->nullable()->after('delivery_certificate_signed');
        });

        /* Brokerage §1: the seller's own questionnaire. */
        Schema::table('listings', function (Blueprint $table): void {
            $table->string('ownership_status', 32)->nullable()->after('yacht_owner_id'); // sole·joint·corporate·trust
            $table->boolean('has_finance')->default(false)->after('ownership_status');
            $table->string('finance_provider', 190)->nullable()->after('has_finance');
            $table->decimal('outstanding_finance', 15, 2)->nullable()->after('finance_provider');
            $table->string('reason_for_sale', 190)->nullable()->after('outstanding_finance');
            $table->string('seller_timeline', 32)->nullable()->after('reason_for_sale'); // immediate·3_months·6_months·no_rush
            // Marketing launch — flowchart §2.
            $table->boolean('listed_on_website')->default(false)->after('is_published');
            $table->boolean('listed_on_portals')->default(false)->after('listed_on_website');
            $table->boolean('listed_on_social')->default(false)->after('listed_on_portals');
            $table->boolean('shared_with_broker_network')->default(false)->after('listed_on_social');
        });

        /* Charter §7: invoices that repeat. */
        Schema::table('invoices', function (Blueprint $table): void {
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->string('recurrence', 16)->nullable()->after('is_recurring'); // weekly·monthly·quarterly·annually
            $table->date('recurrence_until')->nullable()->after('recurrence');
            $table->date('next_issue_on')->nullable()->after('recurrence_until');
            $table->foreignId('recurring_parent_id')->nullable()->after('next_issue_on')
                ->constrained('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('recurring_parent_id');
            $table->dropColumn(['is_recurring', 'recurrence', 'recurrence_until', 'next_issue_on']);
        });

        Schema::table('listings', function (Blueprint $table): void {
            $table->dropColumn([
                'ownership_status', 'has_finance', 'finance_provider', 'outstanding_finance',
                'reason_for_sale', 'seller_timeline', 'listed_on_website', 'listed_on_portals',
                'listed_on_social', 'shared_with_broker_network',
            ]);
        });

        Schema::table('handovers', function (Blueprint $table): void {
            $table->dropColumn([
                'pre_delivery_inspection_done', 'documentation_package_prepared',
                'crew_briefing_acknowledged', 'client_acceptance_signed',
                'delivery_certificate_signed', 'delivery_certificate_path',
            ]);
        });

        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('valuations');
    }
};
