<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            // [{days_before: 30, fee_pct: 0}, …] — tiers, not a single number.
            $table->json('rules')->nullable();
            $table->string('applies_to', 24)->default('charter');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('charter_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();

            $table->string('experience_type', 48)->nullable();
            $table->string('occasion', 48)->nullable();
            $table->date('requested_date')->nullable();
            $table->json('alternative_dates')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->unsignedSmallInteger('guests_adults')->default(0);
            $table->unsignedSmallInteger('guests_children')->default(0);

            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->char('currency', 3)->default('AED');

            $table->foreignId('pickup_marina_id')->nullable()->constrained('marinas')->nullOnDelete();
            $table->foreignId('dropoff_marina_id')->nullable()->constrained('marinas')->nullOnDelete();
            $table->foreignId('yacht_preference_id')->nullable()->constrained('yachts')->nullOnDelete();

            $table->text('itinerary_notes')->nullable();
            $table->json('requested_extras')->nullable();
            $table->text('notes')->nullable();

            $table->string('status', 32)->default('new'); // new·matching·proposed·won·lost·cancelled
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_user_id']);
            $table->index('requested_date');
        });

        // Match scores are explainable: the reasons are stored, never a
        // black-box number a broker cannot defend to a client.
        Schema::create('charter_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('charter_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->json('reasons')->nullable();
            $table->boolean('is_shortlisted')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['charter_enquiry_id', 'yacht_id', 'deleted_at'], 'charter_match_unique');
        });

        Schema::create('charter_proposals', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('charter_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('supersedes_id')->nullable()->constrained('charter_proposals')->nullOnDelete();

            $table->date('valid_until')->nullable();
            $table->char('currency', 3)->default('AED');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('pdf_document_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->string('status', 24)->default('draft'); // draft·sent·viewed·accepted·declined·expired
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->string('decline_reason', 190)->nullable();

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['charter_enquiry_id', 'status']);
        });

        Schema::create('proposal_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('charter_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24)->default('charter'); // charter·extra·discount
            $table->string('category', 48)->nullable();
            $table->string('description_en', 190);
            $table->string('description_ar', 190)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 24)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_treatment', 24)->default('standard');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('charter_proposal_id');
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('charter_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('charter_enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('yacht_id')->constrained();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();

            // Stored UTC, derived from the departure marina's timezone (D-010).
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->foreignId('departure_marina_id')->nullable()->constrained('marinas')->nullOnDelete();
            $table->foreignId('return_marina_id')->nullable()->constrained('marinas')->nullOnDelete();

            $table->unsignedSmallInteger('guests_adults')->default(0);
            $table->unsignedSmallInteger('guests_children')->default(0);
            $table->text('special_requests')->nullable();
            $table->text('itinerary')->nullable();

            $table->string('status', 32)->default('draft');
            // draft·pending_contract·contract_signed·deposit_pending·confirmed·in_progress·completed·cancelled·no_show

            $table->foreignId('contract_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->dateTime('contract_signed_at')->nullable();

            // The pivot the whole operations side gates on.
            $table->dateTime('operational_release_at')->nullable();
            $table->foreignId('operational_release_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('boarded_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->foreignId('cancellation_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason', 190)->nullable();
            $table->decimal('cancellation_fee', 15, 2)->nullable();

            $table->decimal('apa_amount', 15, 2)->nullable();
            $table->char('currency', 3)->default('AED');

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'starts_at', 'ends_at'], 'booking_calendar_index');
            $table->index(['status', 'starts_at']);
            $table->index('client_id');
        });

        Schema::create('booking_guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('name', 190);
            $table->string('nationality', 90)->nullable();
            $table->string('document_type', 24)->nullable();
            $table->text('document_number')->nullable();   // encrypted
            $table->text('date_of_birth')->nullable();     // encrypted
            $table->boolean('is_lead_guest')->default(false);
            $table->text('dietary')->nullable();           // encrypted
            $table->text('allergies')->nullable();         // encrypted
            $table->boolean('id_verified')->default(false);
            $table->foreignId('id_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('checked_in_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('booking_id');
        });

        Schema::create('guest_manifests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format', 16)->default('pdf');
            $table->string('submitted_to', 120)->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // The Cost & Offer table as one object with three phases (D-011).
        Schema::create('cost_sheets', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->char('currency', 3)->default('AED');
            $table->decimal('exchange_rate', 12, 6)->default(1);

            $table->decimal('total_offer', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->decimal('margin_pct', 6, 2)->default(0);

            $table->string('status', 24)->default('draft'); // draft·quoted·invoiced·actual·closed
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['booking_id', 'deleted_at']);
        });

        Schema::create('cost_sheet_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 16)->default('quoted');   // quoted·invoiced·actual
            $table->string('section', 16);                    // revenue·cost
            $table->string('category', 48);                   // seeded from the client's own table
            $table->string('description', 190)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_treatment', 24)->default('standard');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->foreignId('vendor_id')->nullable();
            $table->foreignId('crew_id')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['cost_sheet_id', 'phase', 'section'], 'cost_sheet_line_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_sheet_lines');
        Schema::dropIfExists('cost_sheets');
        Schema::dropIfExists('guest_manifests');
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('proposal_items');
        Schema::dropIfExists('charter_proposals');
        Schema::dropIfExists('charter_matches');
        Schema::dropIfExists('charter_enquiries');
        Schema::dropIfExists('cancellation_policies');
    }
};
