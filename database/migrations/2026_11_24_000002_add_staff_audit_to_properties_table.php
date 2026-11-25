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
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'created_by_user_id')) {
                    $table->foreignId('created_by_user_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('users')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('properties', 'reviewed_by_user_id')) {
                    $table->foreignId('reviewed_by_user_id')
                        ->nullable()
                        ->after('admin_review_note')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'reviewed_by_user_id')) {
                    $table->dropForeign(['reviewed_by_user_id']);
                    $table->dropColumn('reviewed_by_user_id');
                }
                if (Schema::hasColumn('properties', 'created_by_user_id')) {
                    $table->dropForeign(['created_by_user_id']);
                    $table->dropColumn('created_by_user_id');
                }
            });
        }
    }
};
