<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 100); // 'Already Sold', 'Fraudulent', 'Incorrect Price', 'Fake Photos'
            $table->text('details')->nullable();
            $table->string('status', 30)->default('Pending'); // 'Pending', 'Investigated', 'Dismissed', 'Delisted'
            $table->text('admin_action_taken')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_reports');
    }
};

