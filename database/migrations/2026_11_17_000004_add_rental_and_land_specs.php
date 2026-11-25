<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'security_deposit')) {
                $table->decimal('security_deposit', 15, 2)->nullable()->after('price_display');
            }
            if (!Schema::hasColumn('properties', 'maintenance_charges')) {
                $table->string('maintenance_charges', 60)->nullable()->after('security_deposit');
            }
            if (!Schema::hasColumn('properties', 'preferred_tenant')) {
                $table->string('preferred_tenant', 60)->nullable()->after('maintenance_charges');
            }
            if (!Schema::hasColumn('properties', 'plot_frontage')) {
                $table->string('plot_frontage', 60)->nullable()->after('area_type');
            }
            if (!Schema::hasColumn('properties', 'boundary_wall')) {
                $table->string('boundary_wall', 30)->nullable()->after('plot_frontage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = ['security_deposit', 'maintenance_charges', 'preferred_tenant', 'plot_frontage', 'boundary_wall'];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('properties', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
