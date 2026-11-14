<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 30)->unique()->nullable()->index(); // e.g. '032', 'PW-101'
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('posted_by_role', 30)->default('Owner'); // 'Owner', 'Agent', 'Builder', 'Banker'
            $table->string('intent', 25)->default('Sell'); // 'Sell', 'Rent', 'Bank Auction'
            $table->string('asset_type', 25)->default('Property'); // 'Property', 'Vehicle'
            $table->string('property_sub_type', 80); // 'Apartment / Flat', 'Plot / NA Land', 'Car / SUV', etc.
            
            // Basic & Pricing
            $table->string('title', 220);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('price_display', 60);
            $table->string('price_per_sqft', 60)->nullable();
            $table->boolean('is_negotiable')->default(true);
            
            // Location
            $table->string('society_or_yard', 140)->nullable();
            $table->string('locality', 160);
            $table->string('city', 80)->default('Surat');
            $table->string('state', 80)->default('Gujarat');
            $table->string('pincode', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Real Estate Specs
            $table->string('bhk', 20)->nullable();
            $table->string('bathrooms', 20)->nullable();
            $table->string('balconies', 20)->nullable();
            $table->decimal('area_value', 10, 2)->nullable();
            $table->string('area_unit', 30)->nullable(); // 'Sq.Ft', 'Vigha', 'Acre', etc.
            $table->string('area_type', 40)->nullable(); // 'Carpet Area', 'Built-up Area', etc.
            $table->decimal('super_area_value', 10, 2)->nullable();
            $table->string('furnishing', 30)->nullable();
            $table->json('amenities')->nullable();
            
            // Seized Vehicle Specs (Only for Banker Vehicles)
            $table->string('vehicle_category', 40)->nullable(); // 'Car / SUV', 'Commercial Truck', etc.
            $table->string('vehicle_model', 120)->nullable();
            $table->integer('vehicle_make_year')->nullable();
            $table->integer('vehicle_km_driven')->nullable();
            $table->string('vehicle_owner_type', 30)->nullable();
            $table->string('vehicle_fuel_type', 30)->nullable();
            $table->string('vehicle_transmission', 30)->nullable();
            $table->string('vehicle_reg_number', 30)->nullable();
            $table->json('vehicle_inspection_features')->nullable();
            
            // Bank Auction / SARFAESI
            $table->boolean('is_bank_auction')->default(false);
            $table->string('bank_name', 80)->nullable();
            $table->string('auction_date', 60)->nullable();
            $table->timestamp('auction_timestamp')->nullable();
            $table->decimal('reserve_price', 15, 2)->nullable();
            $table->decimal('emd_amount', 15, 2)->nullable();
            $table->string('borrower_name', 120)->nullable();
            $table->string('bank_branch_or_yard', 140)->nullable();
            $table->string('notice_pdf_url', 500)->nullable();
            $table->string('auction_status', 30)->default('Upcoming')->nullable();
            
            // Seller Contact & Moderation
            $table->string('contact_phone', 20);
            $table->string('status', 30)->default('Under Review'); // 'Active', 'Under Review', 'Sold / Rented', 'Rejected'
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('leads_count')->default(0);
            $table->text('admin_review_note')->nullable();
            
            // Soft Delete & Timestamps
            $table->boolean('is_deleted')->default(false);
            $table->softDeletes();
            $table->timestamps();

            // Compound Indexes for fast feed queries
            $table->index(['city', 'intent', 'asset_type', 'status']);
            $table->index(['is_bank_auction', 'bank_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

