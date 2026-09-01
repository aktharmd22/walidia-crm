<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The vault. Files live on a private disk and are only ever served
        // through a policy check plus a five-minute signed URL (D-015).
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('subject_type', 64)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('category', 48)->default('other'); // kyc·contract·certificate·invoice·proposal·survey·statement·other
            $table->string('title', 190);
            $table->text('description')->nullable();

            $table->string('disk', 32)->default('private');
            $table->string('path', 255);
            $table->string('original_name', 190);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('version')->default(1);

            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->unsignedSmallInteger('reminder_days')->default(30);

            $table->string('visibility', 24)->default('internal'); // internal·client·owner·portal
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->dateTime('signed_at')->nullable();

            $table->string('status', 24)->default('active'); // active·superseded·expired·void
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['category', 'status']);
            $table->index('expires_on');
        });

        Schema::create('document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('path', 255);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->string('note', 190)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'version']);
        });

        Schema::create('document_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 190);
            $table->string('type', 32); // proposal·contract·ypa·nda·statement·invoice·manifest
            $table->string('business_line', 24)->nullable();
            $table->longText('body_html')->nullable();
            $table->json('variables')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Provider-agnostic e-signature, with a built-in fallback page (Q15).
        Schema::create('signature_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->default('internal');
            $table->string('provider_ref', 120)->nullable();
            $table->foreignId('signer_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('signer_name', 190);
            $table->string('signer_email', 190);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->string('decline_reason', 190)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('audit_trail')->nullable();
            $table->string('status', 24)->default('draft'); // draft·sent·viewed·signed·declined·expired
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // Seven-day, single-purpose, session-free client links (brief §4).
        Schema::create('signed_links', function (Blueprint $table): void {
            $table->id();
            $table->string('token_hash', 64)->unique();
            $table->string('purpose', 32); // itinerary_approval·signature·payment·feedback·document
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('expires_at');
            $table->unsignedSmallInteger('max_uses')->default(20);
            $table->unsignedSmallInteger('used_count')->default(0);
            $table->dateTime('last_used_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signed_links');
        Schema::dropIfExists('signature_requests');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
