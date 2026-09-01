<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ── Crew ─────────────────────────────────────────────────────────── */

        Schema::create('crew', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('first_name', 90);
            $table->string('last_name', 90)->nullable();
            $table->string('full_name', 190);
            $table->string('role', 48);              // captain·engineer·deckhand·steward·chef
            $table->string('employment_type', 24)->default('employee'); // employee·freelance
            $table->string('nationality', 90)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('email', 190)->nullable();

            // Identity data, encrypted like every other person record.
            $table->text('passport_number')->nullable();
            $table->text('emirates_id')->nullable();
            $table->text('date_of_birth')->nullable();
            $table->text('bank_details')->nullable();

            $table->decimal('day_rate', 12, 2)->nullable();
            $table->char('currency', 3)->default('AED');
            $table->foreignId('home_marina_id')->nullable()->constrained('marinas')->nullOnDelete();
            $table->foreignId('primary_yacht_id')->nullable()->constrained('yachts')->nullOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('active'); // active·on_leave·inactive

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'status']);
            $table->index('full_name');
        });

        // Expiry here is a hard gate on dispatch, and a soft one 30 days out.
        Schema::create('crew_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew')->cascadeOnDelete();
            $table->string('type', 48);  // visa·seaman_book·stcw·medical·licence·passport
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('number')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('valid'); // valid·expiring·expired·missing
            $table->timestamps();
            $table->softDeletes();

            $table->index(['crew_id', 'type']);
            $table->index('expires_on');
        });

        Schema::create('crew_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew')->cascadeOnDelete();
            $table->string('assignable_type', 64);
            $table->unsignedBigInteger('assignable_id');
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('role', 48)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('day_rate', 12, 2)->nullable();
            $table->string('status', 24)->default('proposed'); // proposed·confirmed·declined·completed
            $table->dateTime('acknowledged_at')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assignable_type', 'assignable_id']);
            $table->index(['crew_id', 'starts_at']);
        });

        Schema::create('crew_payouts', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('crew_id')->constrained('crew')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('days', 6, 2)->default(0);
            $table->decimal('day_rate', 12, 2)->default(0);
            $table->decimal('tips_amount', 12, 2)->default(0);
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->char('currency', 3)->default('AED');
            $table->string('status', 24)->default('draft'); // draft·approved·paid
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['crew_id', 'status']);
        });

        /* ── Vendors ──────────────────────────────────────────────────────── */

        Schema::create('vendor_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('business_line', 24)->nullable();
            $table->boolean('requires_insurance')->default(false);
            $table->boolean('requires_licence')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('legal_name', 190);
            $table->string('trade_name', 190)->nullable();
            $table->foreignId('vendor_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('trn')->nullable();
            $table->string('trade_licence_no', 64)->nullable();
            $table->date('licence_expiry')->nullable();
            $table->string('contact_name', 120)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(0);
            $table->text('bank_details')->nullable();
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->string('status', 24)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_category_id', 'status']);
        });

        Schema::create('vendor_ratings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('punctuality')->nullable();
            $table->unsignedTinyInteger('quality')->nullable();
            $table->unsignedTinyInteger('value')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
        });

        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_line', 24)->default('charter');
            $table->date('issued_on')->nullable();
            $table->date('required_by')->nullable();
            $table->char('currency', 3)->default('AED');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 24)->default('draft'); // draft·pending_approval·approved·sent·received·invoiced·paid·cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index('booking_id');
        });

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('description', 190);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 24)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->timestamps();
        });

        /* ── The charter day ──────────────────────────────────────────────── */

        // Append-only: a correction is a new entry referencing the original.
        Schema::create('charter_day_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 32);
            // departure·arrival·guest_boarded·incident·request·extra_charge·status_update·fuel·note·correction
            $table->dateTime('occurred_at');
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location', 190)->nullable();
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->json('photo_paths')->nullable();
            $table->foreignId('corrects_id')->nullable()->constrained('charter_day_logs')->nullOnDelete();
            // Written from a phone on poor signal, so the queue timestamp matters.
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'occurred_at']);
        });

        Schema::create('charter_extras', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('source', 24)->default('guest_request'); // guest_request·upsell
            $table->string('description', 190);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 24)->default('requested'); // requested·approved·delivered·charged·declined
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cost_sheet_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('incidents', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('yacht_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 48);
            $table->string('severity', 24)->default('minor'); // minor·moderate·major·critical
            $table->dateTime('occurred_at');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->text('immediate_action')->nullable();
            $table->boolean('injuries')->default(false);
            $table->boolean('authorities_notified')->default(false);
            $table->string('insurance_claim_ref', 90)->nullable();
            $table->json('photo_paths')->nullable();
            $table->string('status', 24)->default('open'); // open·investigating·closed
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'severity']);
            $table->index('booking_id');
        });

        // Closing this is what releases the security deposit.
        Schema::create('damage_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('discovered_at');
            $table->foreignId('discovered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->json('photo_paths')->nullable();
            $table->boolean('deduct_from_deposit')->default(false);
            $table->string('inspection_status', 24)->default('pending'); // pending·in_progress·closed
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'inspection_status']);
        });

        Schema::create('charter_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->unsignedTinyInteger('nps')->nullable();
            $table->json('ratings')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('follow_up_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charter_feedback');
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('charter_extras');
        Schema::dropIfExists('charter_day_logs');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('vendor_ratings');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('vendor_categories');
        Schema::dropIfExists('crew_payouts');
        Schema::dropIfExists('crew_assignments');
        Schema::dropIfExists('crew_documents');
        Schema::dropIfExists('crew');
    }
};
