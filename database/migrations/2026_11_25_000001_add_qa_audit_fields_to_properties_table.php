<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'auction_portal_url')) {
                $table->string('auction_portal_url', 500)->nullable()->after('notice_pdf_url');
            }
            if (!Schema::hasColumn('properties', 'borrower_loan_account')) {
                $table->string('borrower_loan_account', 120)->nullable()->after('borrower_name');
            }
            if (!Schema::hasColumn('properties', 'sold_date')) {
                $table->date('sold_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('properties', 'featured_until')) {
                $table->date('featured_until')->nullable()->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = ['auction_portal_url', 'borrower_loan_account', 'sold_date', 'featured_until'];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('properties', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
