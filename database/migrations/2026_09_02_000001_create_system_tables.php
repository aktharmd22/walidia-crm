<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gapless human-facing identifiers, issued under a row lock (D-013).
        Schema::create('sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64);
            $table->string('prefix', 16);
            $table->string('period', 16)->default('yearly');   // none · yearly · monthly
            $table->string('period_key', 16)->default('');     // 2026 · 2026-03 · ''
            $table->unsignedBigInteger('current_value')->default(0);
            $table->unsignedTinyInteger('padding')->default(4);
            $table->timestamps();

            $table->unique(['key', 'period_key']);
        });

        // Settings → Lists: experience types, incident categories, and every
        // other dropdown the business wants to edit without a deployment.
        Schema::create('list_options', function (Blueprint $table): void {
            $table->id();
            $table->string('list_key', 64);
            $table->string('value', 64);
            $table->string('label_en', 120);
            $table->string('label_ar', 120)->nullable();
            $table->string('colour_token', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['list_key', 'value', 'deleted_at']);
            $table->index(['list_key', 'is_active']);
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 64);
            $table->string('colour_token', 32)->default('neutral');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'deleted_at']);
        });

        Schema::create('taggables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('taggable_type', 64);
            $table->unsignedBigInteger('taggable_id');
            $table->timestamps();

            $table->unique(['tag_id', 'taggable_type', 'taggable_id'], 'taggables_unique');
            $table->index(['taggable_type', 'taggable_id']);
        });

        // Company profile, TRN, tax defaults, rate cards.
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 64);
            $table->string('key', 120);
            $table->json('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('list_options');
        Schema::dropIfExists('sequences');
    }
};
