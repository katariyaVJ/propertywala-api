<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('seller_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name', 120);
            $table->string('buyer_phone', 20);
            $table->string('buyer_email', 160)->nullable();
            $table->string('intent', 30)->default('Self Use');
            $table->string('timeline', 40)->default('Immediately');
            $table->string('source', 50)->default('Contact Seller');
            $table->string('status', 30)->default('New'); // 'New', 'Contacted', 'Closed', 'Spam'
            $table->timestamps();

            $table->index(['seller_user_id', 'created_at']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_leads');
    }
};

