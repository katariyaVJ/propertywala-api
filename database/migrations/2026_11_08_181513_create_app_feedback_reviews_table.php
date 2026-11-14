<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_feedback_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_phone', 20)->nullable();
            $table->integer('rating')->default(5);
            $table->text('feedback_text')->nullable();
            $table->string('app_version', 20)->default('1.0.0');
            $table->string('platform', 20)->default('Android');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_feedback_reviews');
    }
};

