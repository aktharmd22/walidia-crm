<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Management: running someone else's yacht for them.
 *
 * The certificate register is the load-bearing table here — an expired safety
 * certificate is a charter that cannot sail, which is why dispatch reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_agreements', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_owner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            // full·technical·crew_only·charter_only — what we are actually on the hook for.
            $table->string('scope', 24)->default('full');
            $table->string('fee_model', 24)->default('fixed'); // fixed·percentage·hybrid
            $table->decimal('monthly_fee', 12, 2)->nullable();
            $table->decimal('fee_percentage', 5, 2)->nullable();
            $table->string('currency', 3)->default('AED');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedSmallInteger('notice_days')->default(90);
            $table->decimal('opex_budget_annual', 15, 2)->nullable();
            $table->string('status', 24)->default('active'); // draft·active·expiring·ended
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'ends_on']);
        });

        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            // safety·radio·load_line·registry·insurance·mca·flag·tonnage
            $table->string('type', 48);
            $table->string('name', 190);
            $table->string('number', 90)->nullable();
            $table->string('issued_by', 190)->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            // Some certificates block a charter outright; others are paperwork.
            $table->boolean('blocks_charter')->default(true);
            $table->string('document_path')->nullable();
            $table->string('status', 24)->default('valid'); // valid·expiring·expired·renewing
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'expires_on']);
            $table->index(['blocks_charter', 'expires_on']);
        });

        Schema::create('maintenance_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('management_agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 48)->default('routine'); // routine·repair·refit·warranty·survey
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('priority', 16)->default('normal'); // low·normal·high·critical
            $table->date('due_on')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->string('currency', 3)->default('AED');
            $table->boolean('owner_approval_required')->default(false);
            $table->dateTime('owner_approved_at')->nullable();
            $table->boolean('blocks_charter')->default(false);
            $table->string('status', 24)->default('open'); // open·scheduled·in_progress·done·cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'status', 'due_on']);
        });

        Schema::create('owner_statements', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('management_agreement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('charter_revenue', 15, 2)->default(0);
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->decimal('operating_costs', 15, 2)->default(0);
            $table->decimal('maintenance_costs', 15, 2)->default(0);
            $table->decimal('crew_costs', 15, 2)->default(0);
            $table->decimal('net_to_owner', 15, 2)->default(0);
            $table->string('currency', 3)->default('AED');
            // draft·issued·approved·paid — a statement is not sent by accident.
            $table->string('status', 24)->default('draft');
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['management_agreement_id', 'period_start', 'period_end'], 'owner_statement_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_statements');
        Schema::dropIfExists('maintenance_jobs');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('management_agreements');
    }
};
