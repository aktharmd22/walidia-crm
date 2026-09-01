<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The 360° timeline. Polymorphic so a call logged against a lead today
        // still reads correctly against the booking it becomes tomorrow (D-006).
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 32);            // call·whatsapp·email·meeting·note·status_change·system·gate
            $table->string('direction', 16)->nullable(); // inbound·outbound
            $table->string('summary', 255);
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'activities_subject_index');
            $table->index(['client_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
        });

        // The "Next Action" object from the flowcharts.
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('type', 32)->default('next_action'); // next_action·follow_up·approval·ops·compliance
            $table->string('priority', 16)->default('normal');  // low·normal·high·urgent

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_role', 32)->nullable();
            $table->dateTime('due_at')->nullable();

            $table->string('status', 24)->default('open');      // open·done·cancelled
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('escalate_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('escalated_at')->nullable();

            $table->string('source', 24)->default('manual');    // manual·workflow·gate·reminder
            $table->string('source_key', 64)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_user_id', 'status', 'due_at'], 'tasks_inbox_index');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['status', 'due_at']);
        });

        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(true);
            $table->boolean('is_vip')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->string('disk', 32)->default('private');
            $table->string('path', 255);
            $table->string('original_name', 190);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('activities');
    }
};
