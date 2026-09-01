<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brokerage: selling a yacht rather than chartering one.
 *
 * The path is listing → NDA → viewing → offer → survey → transaction →
 * handover, and the gate engine stands at three points on it: no viewing
 * without a signed NDA and a verified buyer, no offer without proof of funds,
 * and no ownership transfer until the money has cleared and AML is clear.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_owner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            // central·co_central·open — who may sell it, and on what terms.
            $table->string('mandate_type', 24)->default('central');
            $table->decimal('asking_price', 15, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('reserve_price', 15, 2)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->date('agreement_signed_on')->nullable();
            $table->date('agreement_expires_on')->nullable();
            $table->boolean('requires_proof_of_funds')->default(true);
            $table->boolean('requires_nda')->default(true);
            $table->boolean('is_published')->default(false);
            $table->date('listed_on')->nullable();
            // draft·active·under_offer·sold·withdrawn·expired
            $table->string('status', 24)->default('draft');
            $table->text('marketing_summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'agreement_expires_on']);
        });

        Schema::create('buyer_requirements', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->unsignedSmallInteger('loa_min')->nullable();
            $table->unsignedSmallInteger('loa_max')->nullable();
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->json('preferred_builders')->nullable();
            $table->json('regions')->nullable();
            $table->string('use_case', 48)->nullable();
            $table->string('status', 24)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ndas', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('listing_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 24)->default('listing'); // listing·fleet
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->date('expires_on')->nullable();
            $table->string('document_path')->nullable();
            $table->string('status', 24)->default('draft'); // draft·sent·signed·expired
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'listing_id', 'status']);
        });

        Schema::create('viewings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('marina_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(90);
            $table->string('attendees')->nullable();
            // requested·scheduled·completed·cancelled·no_show
            $table->string('status', 24)->default('requested');
            $table->text('feedback')->nullable();
            $table->unsignedTinyInteger('interest_level')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['listing_id', 'scheduled_at']);
        });

        Schema::create('offers', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->boolean('subject_to_survey')->default(true);
            $table->boolean('subject_to_sea_trial')->default(true);
            $table->boolean('proof_of_funds_received')->default(false);
            $table->date('valid_until')->nullable();
            $table->text('conditions')->nullable();
            // draft·submitted·countered·accepted·rejected·withdrawn·lapsed
            $table->string('status', 24)->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->text('response_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['listing_id', 'status']);
        });

        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('listing_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 24)->default('condition'); // condition·sea_trial·valuation
            $table->string('surveyor_name', 190)->nullable();
            $table->string('surveyor_company', 190)->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('paid_by', 16)->default('buyer'); // buyer·seller·shared
            $table->string('outcome', 24)->nullable(); // clear·defects·failed
            $table->text('findings')->nullable();
            $table->decimal('remediation_estimate', 15, 2)->nullable();
            $table->string('report_path')->nullable();
            $table->string('status', 24)->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('seller_owner_id')->nullable()->constrained('yacht_owners')->nullOnDelete();
            $table->decimal('agreed_price', 15, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->dateTime('deposit_cleared_at')->nullable();
            $table->decimal('balance_amount', 15, 2)->nullable();
            $table->dateTime('balance_cleared_at')->nullable();
            $table->string('escrow_agent', 190)->nullable();
            $table->string('contract_type', 24)->default('myba'); // myba·moa·bespoke
            $table->date('contract_signed_on')->nullable();
            $table->date('expected_closing_on')->nullable();
            $table->boolean('aml_cleared')->default(false);
            $table->dateTime('aml_cleared_at')->nullable();
            $table->foreignId('aml_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('ownership_transferred_at')->nullable();
            $table->foreignId('ownership_transferred_by')->nullable()->constrained('users')->nullOnDelete();
            // draft·under_contract·funds_pending·transferring·completed·aborted
            $table->string('status', 24)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expected_closing_on']);
        });

        Schema::create('handovers', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marina_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->boolean('keys_handed_over')->default(false);
            $table->boolean('documents_handed_over')->default(false);
            $table->boolean('inventory_signed')->default(false);
            $table->boolean('flag_registration_updated')->default(false);
            $table->boolean('insurance_transferred')->default(false);
            $table->text('outstanding_items')->nullable();
            $table->string('status', 24)->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // The pipeline deal from Phase 2 becomes the P&L record: one deal, one
        // set of numbers, whichever line earned it.
        Schema::table('deals', function (Blueprint $table): void {
            $table->foreignId('transaction_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->after('transaction_id')->constrained()->nullOnDelete();
            $table->string('line', 16)->default('charter')->after('booking_id');
            $table->decimal('gross_value', 15, 2)->default(0)->after('value');
            $table->decimal('commission_amount', 15, 2)->default(0)->after('gross_value');
            $table->decimal('co_broker_amount', 15, 2)->default(0)->after('commission_amount');
            $table->decimal('costs_amount', 15, 2)->default(0)->after('co_broker_amount');
            $table->decimal('net_amount', 15, 2)->default(0)->after('costs_amount');
            $table->boolean('payouts_issued')->default(false)->after('net_amount');
            $table->boolean('receipts_generated')->default(false)->after('payouts_issued');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('transaction_id');
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn([
                'line', 'gross_value', 'commission_amount', 'co_broker_amount',
                'costs_amount', 'net_amount', 'payouts_issued', 'receipts_generated',
            ]);
        });
        Schema::dropIfExists('handovers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('viewings');
        Schema::dropIfExists('ndas');
        Schema::dropIfExists('buyer_requirements');
        Schema::dropIfExists('listings');
    }
};
