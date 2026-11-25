<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Bank Auction Authority & Identifiers
            if (!Schema::hasColumn('properties', 'auction_by')) {
                $table->string('auction_by', 60)->nullable()->default('Bank (SARFAESI)')->after('is_bank_auction');
            }
            if (!Schema::hasColumn('properties', 'bank_asset_id')) {
                $table->string('bank_asset_id', 60)->nullable()->after('auction_by');
            }
            if (!Schema::hasColumn('properties', 'action_type')) {
                $table->string('action_type', 60)->nullable()->default('Under Hypothecation')->after('bank_asset_id');
            }

            // Extended Vehicle Specifications (from Bank Seizure / e-Auction Portals)
            if (!Schema::hasColumn('properties', 'vehicle_brand')) {
                $table->string('vehicle_brand', 80)->nullable()->after('vehicle_category');
            }
            if (!Schema::hasColumn('properties', 'vehicle_variant')) {
                $table->string('vehicle_variant', 60)->nullable()->after('vehicle_model');
            }
            if (!Schema::hasColumn('properties', 'body_type')) {
                $table->string('body_type', 60)->nullable()->after('vehicle_variant');
            }
            if (!Schema::hasColumn('properties', 'vehicle_color')) {
                $table->string('vehicle_color', 40)->nullable()->after('body_type');
            }
            if (!Schema::hasColumn('properties', 'engine_number')) {
                $table->string('engine_number', 60)->nullable()->after('vehicle_reg_number');
            }
            if (!Schema::hasColumn('properties', 'chassis_number')) {
                $table->string('chassis_number', 60)->nullable()->after('engine_number');
            }
            if (!Schema::hasColumn('properties', 'engine_capacity_cc')) {
                $table->integer('engine_capacity_cc')->nullable()->after('chassis_number');
            }
            if (!Schema::hasColumn('properties', 'plate_type')) {
                $table->string('plate_type', 40)->nullable()->after('vehicle_owner_type');
            }
            if (!Schema::hasColumn('properties', 'rc_status')) {
                $table->string('rc_status', 60)->nullable()->after('plate_type');
            }
            if (!Schema::hasColumn('properties', 'insurance_status')) {
                $table->string('insurance_status', 60)->nullable()->after('rc_status');
            }
            if (!Schema::hasColumn('properties', 'ac_working')) {
                $table->string('ac_working', 20)->nullable()->after('insurance_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = [
                'auction_by',
                'bank_asset_id',
                'action_type',
                'vehicle_brand',
                'vehicle_variant',
                'body_type',
                'vehicle_color',
                'engine_number',
                'chassis_number',
                'engine_capacity_cc',
                'plate_type',
                'rc_status',
                'insurance_status',
                'ac_working',
            ];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('properties', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
