<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('new_projects')) {
            Schema::table('new_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('new_projects', 'state')) {
                    $table->string('state', 100)->nullable()->default('Gujarat')->after('city');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('new_projects')) {
            Schema::table('new_projects', function (Blueprint $table) {
                if (Schema::hasColumn('new_projects', 'state')) {
                    $table->dropColumn('state');
                }
            });
        }
    }
};
