<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marinas', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->string('name_ar', 190)->nullable();
            $table->string('country', 90)->default('United Arab Emirates');
            $table->string('emirate', 90)->nullable();
            $table->string('city', 90)->nullable();
            // Load-bearing: charter instants are derived from the marina's own
            // timezone, never from a naive local string (D-010).
            $table->string('timezone', 64)->default('Asia/Dubai');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_phone', 32)->nullable();
            $table->string('contact_email', 190)->nullable();
            $table->boolean('requires_manifest')->default(false);
            $table->string('manifest_format', 32)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country', 'city']);
        });

        Schema::create('berths', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('marina_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->decimal('max_loa_m', 6, 2)->nullable();
            $table->decimal('monthly_fee', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['marina_id', 'code', 'deleted_at']);
        });

        // One hull, three capability flags (D-003).
        Schema::create('yachts', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->string('name', 190);
            $table->string('name_ar', 190)->nullable();

            $table->boolean('is_charter_fleet')->default(false);
            $table->boolean('is_for_sale')->default(false);
            $table->boolean('is_managed')->default(false);

            $table->string('builder', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->unsignedSmallInteger('year_refit')->nullable();

            $table->decimal('loa_m', 6, 2)->nullable();
            $table->decimal('beam_m', 6, 2)->nullable();
            $table->decimal('draft_m', 6, 2)->nullable();
            $table->unsignedInteger('gross_tonnage')->nullable();
            $table->string('hull_material', 64)->nullable();
            $table->string('exterior_designer', 120)->nullable();
            $table->string('interior_designer', 120)->nullable();

            $table->string('engines', 190)->nullable();
            $table->unsignedInteger('engine_hours')->nullable();
            $table->unsignedSmallInteger('cruising_speed_kn')->nullable();
            $table->unsignedSmallInteger('max_speed_kn')->nullable();
            $table->unsignedInteger('fuel_capacity_l')->nullable();
            $table->unsignedInteger('water_capacity_l')->nullable();

            // Static vs cruising capacity is a licensing limit, not a detail.
            $table->unsignedSmallInteger('capacity_static')->nullable();
            $table->unsignedSmallInteger('capacity_cruising')->nullable();
            $table->unsignedSmallInteger('cabins')->nullable();
            $table->unsignedSmallInteger('berths')->nullable();
            $table->unsignedSmallInteger('crew_count')->nullable();

            $table->string('flag_country', 90)->nullable();
            $table->string('registration_no', 64)->nullable();
            $table->string('imo_no', 32)->nullable();
            $table->string('mmsi', 32)->nullable();

            $table->foreignId('home_marina_id')->nullable()->constrained('marinas')->nullOnDelete();
            $table->foreignId('berth_id')->nullable()->constrained('berths')->nullOnDelete();
            $table->foreignId('owner_client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('status', 24)->default('active'); // active·maintenance·off_market·sold·archived

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_charter_fleet', 'status']);
            $table->index(['is_for_sale', 'status']);
            $table->index(['is_managed', 'status']);
            $table->index('name');
            $table->index('home_marina_id');
        });

        Schema::create('yacht_charter_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('half_day_rate', 12, 2)->nullable();
            $table->decimal('full_day_rate', 12, 2)->nullable();
            $table->decimal('overnight_rate', 12, 2)->nullable();
            $table->decimal('weekly_rate', 15, 2)->nullable();
            $table->decimal('peak_multiplier', 5, 2)->default(1);
            $table->char('currency', 3)->default('AED');
            $table->unsignedSmallInteger('min_hours')->default(3);
            $table->decimal('apa_percentage', 5, 2)->nullable();
            $table->json('included_extras')->nullable();
            $table->unsignedBigInteger('cancellation_policy_id')->nullable();
            $table->boolean('is_bookable')->default(true);
            $table->timestamps();

            $table->unique('yacht_id');
        });

        Schema::create('yacht_sale_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->decimal('asking_price', 15, 2)->nullable();
            $table->char('currency', 3)->default('AED');
            $table->string('price_visibility', 24)->default('on_request'); // public·on_request
            $table->string('vat_status', 24)->nullable();
            $table->boolean('is_price_negotiable')->default(true);
            $table->unsignedBigInteger('last_valuation_id')->nullable();
            $table->timestamps();

            $table->unique('yacht_id');
        });

        Schema::create('yacht_management_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->foreignId('technical_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('budget_annual', 15, 2)->nullable();
            $table->string('reporting_cadence', 24)->default('monthly');
            $table->timestamps();

            $table->unique('yacht_id');
        });

        Schema::create('yacht_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->string('collection', 32)->default('gallery'); // hero·gallery·deck_plan·video·tour
            $table->string('disk', 32)->default('public');
            $table->string('path', 255);
            $table->string('original_name', 190)->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt_en', 190)->nullable();
            $table->string('alt_ar', 190)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Drives what the website sync is allowed to publish (Q17).
            $table->boolean('is_public')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'collection', 'sort_order'], 'yacht_media_order_index');
        });

        Schema::create('yacht_inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->string('category', 64);
            $table->string('name', 190);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 32)->default('good');
            $table->date('last_checked_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'category']);
        });

        // The single source of fleet occupancy: bookings, option holds,
        // maintenance windows and owner use all write here.
        Schema::create('yacht_availability_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('type', 24); // booking·option_hold·maintenance·owner_use·blocked
            $table->string('source_type', 64)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->dateTime('expires_at')->nullable();  // option holds lapse
            $table->string('note', 190)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'starts_at', 'ends_at'], 'yacht_calendar_index');
            $table->index(['source_type', 'source_id']);
            $table->index('expires_at');
        });

        Schema::create('yacht_owners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->decimal('ownership_percentage', 5, 2)->default(100);
            $table->boolean('is_primary')->default(true);
            $table->date('since')->nullable();
            $table->date('until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'client_id']);
        });

        Schema::create('owner_agreements', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->nullable()->unique();
            $table->foreignId('yacht_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('type', 32)->default('charter_revenue_share');
            $table->string('revenue_share_model', 16)->default('net'); // gross·net (Q22)
            $table->decimal('owner_share_pct', 5, 2)->default(70);
            $table->decimal('company_share_pct', 5, 2)->default(30);
            $table->string('statement_frequency', 16)->default('monthly');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->unsignedSmallInteger('notice_days')->default(30);
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->json('deductible_categories')->nullable();
            $table->string('status', 24)->default('draft'); // draft·active·expired·terminated
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['yacht_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_agreements');
        Schema::dropIfExists('yacht_owners');
        Schema::dropIfExists('yacht_availability_blocks');
        Schema::dropIfExists('yacht_inventory_items');
        Schema::dropIfExists('yacht_media');
        Schema::dropIfExists('yacht_management_profiles');
        Schema::dropIfExists('yacht_sale_profiles');
        Schema::dropIfExists('yacht_charter_profiles');
        Schema::dropIfExists('yachts');
        Schema::dropIfExists('berths');
        Schema::dropIfExists('marinas');
    }
};
