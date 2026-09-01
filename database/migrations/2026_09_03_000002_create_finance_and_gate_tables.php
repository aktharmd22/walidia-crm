<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ── Tax ──────────────────────────────────────────────────────────── */

        Schema::create('vat_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('label', 90);
            $table->decimal('rate_pct', 5, 2)->default(0);
            $table->string('treatment', 24); // standard·zero_rated·out_of_scope·reverse_charge
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->char('base', 3);
            $table->char('quote', 3);
            $table->decimal('rate', 16, 8);
            $table->date('rate_date');
            $table->string('source', 64)->nullable();
            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['base', 'quote', 'rate_date']);
        });

        /* ── Billing ──────────────────────────────────────────────────────── */

        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('business_line', 24)->default('charter');
            $table->date('issued_on')->nullable();
            $table->date('valid_until')->nullable();
            $table->char('currency', 3)->default('AED');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 24)->default('draft');
            $table->foreignId('converted_invoice_id')->nullable();
            $table->foreignId('pdf_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('description_en', 190);
            $table->string('description_ar', 190)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_treatment', 24)->default('standard');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            // Gapless and never reissued — not even after voiding (D-013).
            $table->string('reference', 32)->nullable()->unique();
            $table->string('type', 24)->default('tax_invoice'); // tax_invoice·proforma·credit_note

            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreignId('cost_sheet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('credit_note_of_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('place_of_supply', 90)->nullable();
            $table->string('tax_treatment', 24)->default('standard');

            $table->char('currency', 3)->default('AED');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);

            $table->string('supplier_trn', 32)->nullable();
            $table->text('buyer_trn')->nullable(); // encrypted

            $table->string('status', 24)->default('draft'); // draft·issued·partially_paid·paid·overdue·void·credited
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->string('void_reason', 190)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('pdf_document_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_date']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('client_id');
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cost_sheet_line_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description_en', 190);
            $table->string('description_ar', 190)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 24)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_treatment', 24)->default('standard');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('invoice_id');
        });

        Schema::create('payment_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120)->default('Charter payment plan');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->char('currency', 3)->default('AED');
            $table->string('status', 24)->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_schedule_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('label', 48)->default('deposit'); // deposit·balance·final·apa
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->dateTime('due_at')->nullable();
            $table->string('status', 24)->default('pending'); // pending·due·paid·overdue·waived
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['payment_schedule_id', 'sequence']);
            $table->index(['status', 'due_at']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 24)->default('bank_transfer'); // bank_transfer·card·cash·cheque·link
            $table->string('gateway', 32)->nullable();
            $table->string('gateway_reference', 120)->nullable();

            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('AED');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('amount_aed', 15, 2)->default(0);

            $table->dateTime('received_at')->nullable();
            // `cleared_at`, not `received_at`, is what the Operational Release
            // and ownership-transfer gates read.
            $table->dateTime('cleared_at')->nullable();
            $table->string('status', 24)->default('pending'); // pending·cleared·failed·refunded·partially_refunded

            $table->decimal('bank_charge_amount', 12, 2)->nullable();
            $table->decimal('bank_charge_vat', 12, 2)->nullable();

            $table->foreignId('proof_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->dateTime('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'received_at']);
            $table->index('client_id');
        });

        // One transfer routinely settles two invoices.
        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_schedule_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index('payment_id');
            $table->index('invoice_id');
        });

        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('issued_at')->nullable();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('AED');
            $table->foreignId('pdf_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('security_deposits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('AED');
            $table->string('method', 24)->default('card_hold'); // card_hold·cash·transfer
            $table->dateTime('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hold_reference', 120)->nullable();
            $table->string('status', 24)->default('held'); // held·partially_released·released·forfeited
            $table->decimal('released_amount', 15, 2)->nullable();
            $table->dateTime('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deduction_reason', 190)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
        });

        /* ── The gate engine (D-004) ──────────────────────────────────────── */

        Schema::create('gate_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name_en', 190);
            $table->string('name_ar', 190)->nullable();
            $table->text('description')->nullable();

            $table->string('subject_type', 64);
            $table->string('trigger_type', 24)->default('action'); // action·transition·schedule
            $table->string('trigger_field', 64)->nullable();
            $table->json('trigger_from')->nullable();
            $table->string('trigger_to', 64)->nullable();
            $table->string('action_key', 96)->nullable();

            $table->string('severity', 16)->default('hard');  // hard·soft
            $table->json('conditions');
            $table->string('block_message_en', 255);
            $table->string('block_message_ar', 255)->nullable();
            $table->string('resolution_route', 120)->nullable();
            $table->string('resolution_label', 90)->nullable();
            $table->json('creates_task')->nullable();

            $table->boolean('is_overridable')->default(true);
            $table->string('override_permission', 64)->default('gates.override');
            $table->boolean('requires_reason')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'trigger_type', 'is_active'], 'gate_rule_lookup');
        });

        // Every evaluation is kept, pass or fail: this is how "why was this
        // charter allowed to sail" stays answerable a year later.
        Schema::create('gate_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gate_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_key', 96)->nullable();
            $table->string('result', 16); // pass·warn·block
            $table->json('failed_conditions')->nullable();
            $table->json('context')->nullable();
            $table->dateTime('evaluated_at');

            $table->index(['subject_type', 'subject_id', 'evaluated_at'], 'gate_eval_subject');
            $table->index(['result', 'evaluated_at']);
        });

        // Append-only. The Override Register reads this and nothing else.
        Schema::create('gate_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gate_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gate_evaluation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');
            $table->json('failed_conditions')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_overrides');
        Schema::dropIfExists('gate_evaluations');
        Schema::dropIfExists('gate_rules');
        Schema::dropIfExists('security_deposits');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_schedule_items');
        Schema::dropIfExists('payment_schedules');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('vat_rates');
    }
};
