<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'alt_contact_phone')) {
                $table->string('alt_contact_phone', 20)->nullable()->after('contact_phone');
            }
            if (!Schema::hasColumn('properties', 'emd_last_date')) {
                $table->string('emd_last_date', 80)->nullable()->after('emd_amount');
            }
            if (!Schema::hasColumn('properties', 'emd_last_timestamp')) {
                $table->timestamp('emd_last_timestamp')->nullable()->after('emd_last_date');
            }
            if (!Schema::hasColumn('properties', 'possession_type')) {
                $table->string('possession_type', 60)->nullable()->after('notice_pdf_url');
            }
            if (!Schema::hasColumn('properties', 'facing')) {
                $table->string('facing', 30)->nullable()->after('furnishing');
            }
            if (!Schema::hasColumn('properties', 'property_age')) {
                $table->string('property_age', 40)->nullable()->after('facing');
            }
            if (!Schema::hasColumn('properties', 'vehicle_title')) {
                $table->string('vehicle_title', 220)->nullable()->after('vehicle_model');
            }
            if (!Schema::hasColumn('properties', 'vehicle_description')) {
                $table->text('vehicle_description')->nullable()->after('vehicle_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'alt_contact_phone',
                'emd_last_date',
                'emd_last_timestamp',
                'possession_type',
                'facing',
                'property_age',
                'vehicle_title',
                'vehicle_description',
            ], fn ($col) => Schema::hasColumn('properties', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
