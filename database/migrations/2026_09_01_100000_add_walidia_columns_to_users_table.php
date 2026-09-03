<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('job_title', 120)->nullable()->after('avatar_path');

            $table->string('locale', 5)->default('en')->after('job_title');
            $table->string('timezone', 64)->default('Asia/Dubai')->after('locale');
            $table->string('chrome', 16)->default('light')->after('timezone');
            $table->string('accent', 16)->default('brass')->after('chrome');

            $table->boolean('is_active')->default(true)->after('accent');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->softDeletes();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone', 'avatar_path', 'job_title', 'locale', 'timezone',
                'chrome', 'accent', 'is_active', 'last_login_at', 'last_login_ip',
            ]);
        });
    }
};
