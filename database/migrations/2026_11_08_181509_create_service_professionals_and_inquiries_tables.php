<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 80); // 'Interior & Vastu', 'Legal Title Verification', 'Lawyer / Advocate', 'Banker (for Loan)'
            $table->string('name', 120);
            $table->string('firm_name', 150);
            $table->integer('experience_years')->default(3);
            $table->string('experience_display', 60)->default('3+ Years Experience');
            $table->string('phone', 20);
            $table->string('email', 160)->nullable();
            $table->string('city', 80)->default('Surat');
            $table->json('specializations')->nullable();
            $table->text('description')->nullable();
            $table->string('license_or_reg_number', 100)->nullable();
            $table->string('status', 30)->default('Pending'); // 'Pending', 'Approved', 'Rejected'
            $table->text('rejection_reason')->nullable();
            $table->decimal('rating', 2, 1)->default(4.9);
            $table->integer('verified_deals_count')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category', 'status', 'city']);
        });

        Schema::create('service_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained('service_professionals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_name', 120);
            $table->string('user_phone', 20);
            $table->string('bank_name', 80)->nullable();
            $table->decimal('loan_amount_requested', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('Pending'); // 'Pending', 'Attended', 'Closed'
            $table->timestamps();

            $table->index(['professional_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_inquiries');
        Schema::dropIfExists('service_professionals');
    }
};

