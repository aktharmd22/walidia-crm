<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('channel', 32)->default('direct'); // website·whatsapp·referral·agent·event·walk_in
            $table->string('utm_key', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'deleted_at']);
        });

        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('business_line', 24)->default('charter'); // charter·brokerage·management
            $table->foreignId('source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 190);
            $table->string('email', 190)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();

            $table->string('status', 32)->default('new'); // new·contacted·qualified·registered·unqualified·duplicate
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('duplicate_of_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->unsignedTinyInteger('duplicate_score')->nullable();
            $table->dateTime('duplicate_checked_at')->nullable();

            // Response-time reporting and the Follow-Up Pool both read these.
            $table->dateTime('first_response_at')->nullable();
            $table->dateTime('sla_due_at')->nullable();
            $table->dateTime('next_follow_up_at')->nullable();

            $table->dateTime('converted_at')->nullable();
            $table->string('converted_to_type', 64)->nullable();
            $table->unsignedBigInteger('converted_to_id')->nullable();
            $table->string('unqualified_reason', 190)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_user_id']);
            $table->index(['business_line', 'status']);
            $table->index('sla_due_at');
            $table->index('mobile');
            $table->index(['converted_to_type', 'converted_to_id']);
        });

        Schema::create('pipelines', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 32)->unique();  // charter·buyer·seller
            $table->string('name', 120);
            $table->string('name_ar', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('key', 48);
            $table->string('name', 120);
            $table->string('name_ar', 120)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('colour_token', 32)->default('neutral');
            $table->unsignedTinyInteger('probability')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->timestamps();

            $table->unique(['pipeline_id', 'key']);
            $table->index(['pipeline_id', 'sort_order']);
        });

        Schema::create('lost_reasons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pipeline_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // One board for all three pipelines. Stage is the board position;
        // the underlying record keeps its own lifecycle status (D-005).
        Schema::create('deals', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('pipeline_id')->constrained();
            $table->foreignId('stage_id')->constrained('pipeline_stages');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('yacht_id')->nullable();

            $table->string('title', 190);
            $table->decimal('value', 15, 2)->default(0);
            $table->char('currency', 3)->default('AED');
            $table->date('expected_close_date')->nullable();

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('stage_entered_at')->nullable();
            $table->foreignId('lost_reason_id')->nullable()->constrained('lost_reasons')->nullOnDelete();
            $table->text('lost_notes')->nullable();
            $table->string('status', 24)->default('open'); // open·won·lost
            $table->dateTime('closed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_id', 'stage_id', 'assigned_user_id'], 'deals_board_index');
            $table->index(['subject_type', 'subject_id']);
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
        Schema::dropIfExists('lost_reasons');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_sources');
    }
};
