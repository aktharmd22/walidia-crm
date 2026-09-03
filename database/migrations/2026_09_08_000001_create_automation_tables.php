<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The automation engine.
 *
 * Same principle as the gate engine: rules are data, not code. An operations
 * manager who wants the charter reminder to go 48 hours out instead of 24
 * should not need a deployment, and every message the system has ever sent
 * should be answerable from one table when a client asks "did you tell me?".
 */
return new class extends Migration
{
    public function up(): void
    {
        /* What to say, in both languages, with the merge fields it expects. */
        Schema::create('message_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 190);
            // email·whatsapp·sms·in_app — the same message often goes several ways.
            $table->string('channel', 24)->default('email');
            $table->string('subject_en', 190)->nullable();
            $table->string('subject_ar', 190)->nullable();
            $table->text('body_en');
            $table->text('body_ar')->nullable();
            $table->json('merge_fields')->nullable();
            $table->string('category', 32)->default('client'); // client·crew·vendor·internal
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        /*
         * When to say it.
         *
         * A rule is an event or a schedule, an optional offset, an optional
         * condition, and an action. That covers every automation in both
         * flowcharts without a rule builder that can express nonsense.
         */
        Schema::create('workflow_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 190);
            $table->string('description', 500)->nullable();
            $table->string('business_line', 24)->default('charter');
            // event·schedule — fired by something happening, or by the clock.
            $table->string('trigger_type', 16)->default('event');
            $table->string('trigger_event', 64)->nullable();   // booking.confirmed, charter.completed…
            $table->string('subject_type', 64)->nullable();
            // Hours relative to the anchor date; negative is before.
            $table->integer('offset_hours')->default(0);
            $table->string('anchor_field', 64)->nullable();     // starts_at, completed_at, expires_on…
            $table->json('conditions')->nullable();
            // send_message·create_task·notify_role·update_field·issue_document
            $table->string('action', 32);
            $table->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $table->json('action_params')->nullable();
            $table->string('audience', 32)->default('client');  // client·owner·crew·vendor·role
            $table->string('audience_role', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trigger_type', 'trigger_event', 'is_active'], 'workflow_trigger_index');
        });

        /*
         * What was queued, and what happened to it.
         *
         * Every run is recorded whether it sent, skipped or failed — an
         * automation nobody can audit is one nobody trusts.
         */
        Schema::create('workflow_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_rule_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->dateTime('due_at');
            $table->dateTime('ran_at')->nullable();
            // pending·sent·skipped·failed·cancelled
            $table->string('status', 24)->default('pending');
            $table->string('skip_reason', 190)->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->unique(['workflow_rule_id', 'subject_type', 'subject_id'], 'workflow_run_once');
        });

        /*
         * Everything the company has ever sent a client, in one place.
         *
         * "Did you tell me?" is a question a charter business gets asked, and
         * the answer has to be a record rather than someone's memory.
         */
        Schema::create('communications', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 24);
            $table->string('direction', 16)->default('outbound');
            $table->string('to_address', 190)->nullable();
            $table->string('subject', 190)->nullable();
            $table->text('body')->nullable();
            $table->string('status', 24)->default('queued'); // queued·sent·delivered·read·failed
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('provider_reference', 190)->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['client_id', 'sent_at']);
        });

        /*
         * Post-charter and post-sale: the journey a client is walked through
         * after the money is settled, from thank-you to repeat booking.
         */
        Schema::create('client_journeys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24)->default('post_charter'); // post_charter·post_sale
            $table->dateTime('thank_you_sent_at')->nullable();
            $table->dateTime('feedback_requested_at')->nullable();
            $table->dateTime('review_requested_at')->nullable();
            $table->dateTime('survey_sent_at')->nullable();
            $table->unsignedTinyInteger('satisfaction_score')->nullable();
            $table->text('survey_response')->nullable();
            $table->boolean('complaint_raised')->default(false);
            $table->text('complaint_detail')->nullable();
            $table->dateTime('complaint_resolved_at')->nullable();
            $table->text('complaint_resolution')->nullable();
            $table->json('follow_ups_sent')->nullable();     // {"7":"…","30":"…","90":"…"}
            $table->json('upsell_interests')->nullable();    // brokerage·management·maintenance·membership
            $table->string('status', 24)->default('open');   // open·complete·lapsed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'type', 'status']);
        });

        /* Gift vouchers and loyalty — flowchart §15. */
        Schema::create('loyalty_rewards', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24)->default('voucher'); // voucher·points·upgrade·membership
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency', 3)->default('AED');
            $table->unsignedInteger('points')->nullable();
            $table->string('code', 32)->nullable()->unique();
            $table->text('description')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('expires_on')->nullable();
            $table->dateTime('redeemed_at')->nullable();
            $table->foreignId('redeemed_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('status', 24)->default('issued'); // issued·redeemed·expired·cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('client_journeys');
        Schema::dropIfExists('communications');
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('workflow_rules');
        Schema::dropIfExists('message_templates');
    }
};
