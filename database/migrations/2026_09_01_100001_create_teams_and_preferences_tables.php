<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teams exist so "Sales sees own records" can also mean "a team lead
        // sees the team's" without a second visibility mechanism (Q2).
        Schema::create('teams', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('business_line', 32)->nullable();
            $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_in_team', 32)->default('member');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        // Per-user UI state: table columns, density, default filters.
        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 120);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });

        // Saved filter views behind the Filter control on every index screen.
        Schema::create('saved_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 64);
            $table->string('name', 120);
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'module']);
        });

        // Blocks reuse of recent passwords, part of the 12-character policy.
        Schema::create('password_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password_hash');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });

        // "Every VIP record access is logged" — this is that log.
        Schema::create('record_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->string('field_group', 32);         // vip · manifest · financial · document
            $table->string('action', 32);              // view · export · download
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_access_logs');
        Schema::dropIfExists('password_histories');
        Schema::dropIfExists('saved_views');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
