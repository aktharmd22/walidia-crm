<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operations checklists arrive properly in Phase 4, but the boarding gate
     * already reads a named blocking item from them — so the tables exist now,
     * and switching that gate on later is a data change, not a schema change.
     */
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->string('business_line', 24)->default('charter');
            $table->string('trigger', 48)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checklist_template_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('section', 64)->nullable();
            $table->string('title_en', 190);
            $table->string('title_ar', 190)->nullable();
            $table->string('responsible_role', 32)->nullable();
            $table->integer('offset_hours')->default(0);
            $table->boolean('requires_photo')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->boolean('is_blocking')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['checklist_template_id', 'key']);
        });

        Schema::create('operations_checklists', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 24)->default('open');
            $table->unsignedTinyInteger('completion_pct')->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operations_checklist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_template_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key', 64);
            $table->string('title', 190);
            $table->string('section', 64)->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('due_at')->nullable();
            $table->string('status', 24)->default('pending'); // pending·done·na·blocked
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('signature_path', 255)->nullable();
            $table->boolean('is_blocking')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['operations_checklist_id', 'status'], 'checklist_item_status');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('operations_checklists');
        Schema::dropIfExists('checklist_template_items');
        Schema::dropIfExists('checklist_templates');
    }
};
