<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pro_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan_name', 50);
            $table->string('plan_tier', 20)->default('PRO');
            $table->decimal('price_paid', 10, 2);
            $table->string('currency', 5)->default('INR');
            $table->integer('contact_limit')->default(40);
            $table->integer('contacts_used')->default(0);
            $table->integer('opt_lock_version')->default(0);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until');
            $table->string('status', 25)->default('Active');
            $table->string('payment_order_id', 100)->nullable();
            $table->string('payment_id', 100)->nullable();
            $table->string('payment_signature', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_memberships');
    }
};

