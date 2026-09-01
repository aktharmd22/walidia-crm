<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('legal_name', 190);
            $table->string('trade_name', 190)->nullable();
            $table->string('type', 32)->default('corporate'); // corporate·dmc·concierge·charter_partner·broker·supplier
            $table->text('trn')->nullable();                  // encrypted
            $table->string('trn_hash', 64)->nullable();       // blind index (D-007)
            $table->string('trade_licence_no', 64)->nullable();
            $table->date('licence_expiry')->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('website', 190)->nullable();
            $table->string('address_line1', 190)->nullable();
            $table->string('address_line2', 190)->nullable();
            $table->string('city', 90)->nullable();
            $table->string('emirate', 90)->nullable();
            $table->string('country', 90)->nullable();
            $table->string('billing_email', 190)->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(0);
            $table->decimal('commission_rate_default', 5, 2)->nullable();
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('assigned_user_id');
            $table->index('trn_hash');
        });

        // The single client record: one row can be charter guest, buyer,
        // seller and owner at once, so client_type is a JSON array.
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->json('client_type')->nullable();

            $table->string('salutation', 32)->nullable();
            $table->string('first_name', 90);
            $table->string('last_name', 90)->nullable();
            $table->string('full_name', 190);
            $table->string('full_name_ar', 190)->nullable();

            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position', 120)->nullable();

            $table->string('email', 190)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('phone_alt', 32)->nullable();
            $table->string('preferred_channel', 24)->default('whatsapp');

            $table->string('nationality', 90)->nullable();
            $table->string('address_line1', 190)->nullable();
            $table->string('address_line2', 190)->nullable();
            $table->string('city', 90)->nullable();
            $table->string('emirate', 90)->nullable();
            $table->string('country', 90)->nullable();
            $table->date('date_of_birth')->nullable();

            // Encrypted at rest, with blind indexes for exact-match duplicate
            // checking — the only lookup these fields support by design.
            $table->text('passport_number')->nullable();
            $table->string('passport_hash', 64)->nullable();
            $table->date('passport_expiry')->nullable();
            $table->text('emirates_id')->nullable();
            $table->string('emirates_id_hash', 64)->nullable();
            $table->text('trn')->nullable();

            $table->string('vip_level', 24)->default('none'); // none·vip·uhnw·protected
            $table->text('dietary_preferences')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes_vip')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('source_id')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('kyc_status', 24)->default('not_started');
            $table->dateTime('kyc_verified_at')->nullable();
            $table->foreignId('kyc_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('kyc_expires_on')->nullable();

            $table->string('aml_status', 24)->default('not_required');
            $table->dateTime('aml_screened_at')->nullable();

            $table->dateTime('marketing_consent_at')->nullable();
            $table->string('consent_channel', 24)->nullable();

            $table->string('status', 24)->default('active'); // active·dormant·blacklisted·pending_approval
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('assigned_user_id');
            $table->index('company_id');
            $table->index(['status', 'kyc_status']);
            $table->index('vip_level');
            $table->index('mobile');
            $table->index('email');
            $table->index('passport_hash');
            $table->index('emirates_id_hash');
            $table->index('full_name');
        });

        // PAs, family offices and captains contact us, not the principal.
        Schema::create('client_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name', 190);
            $table->string('role', 90)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
        });

        Schema::create('company_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 190);
            $table->string('position', 120)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
        Schema::dropIfExists('client_contacts');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('companies');
    }
};
