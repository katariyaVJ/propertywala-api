<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('state', 80)->default('Gujarat');
            $table->boolean('is_popular')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('famous_localities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('subtitle', 160)->nullable();
            $table->decimal('avg_price_per_sqft', 10, 2)->nullable();
            $table->boolean('is_famous')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('famous_localities');
        Schema::dropIfExists('cities');
    }
};

