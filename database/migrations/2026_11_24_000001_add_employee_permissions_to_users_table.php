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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'admin_permissions')) {
                    $table->json('admin_permissions')->nullable()->after('role');
                }
                if (!Schema::hasColumn('users', 'employee_code')) {
                    $table->string('employee_code', 50)->nullable()->after('admin_permissions');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'employee_code')) {
                    $table->dropColumn('employee_code');
                }
                if (Schema::hasColumn('users', 'admin_permissions')) {
                    $table->dropColumn('admin_permissions');
                }
            });
        }
    }
};
