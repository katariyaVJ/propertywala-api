<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('new_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('developer_name', 140);
            $table->string('developer_stats', 100)->nullable();
            $table->string('locality', 160);
            $table->string('city', 80);
            $table->string('rera_number', 80)->unique();
            $table->string('price_range', 80);
            $table->string('bhk_options', 120);
            $table->string('possession', 60);
            $table->string('construction_stage', 100)->nullable();
            $table->string('total_units', 80)->nullable();
            $table->json('tags')->nullable();
            $table->json('amenities')->nullable();
            $table->text('description')->nullable();
            $table->string('banner_gradient', 100)->nullable();
            $table->string('brochure_url', 500)->nullable();
            $table->boolean('is_featured')->default(true);
            $table->timestamps();

            $table->index(['city', 'is_featured']);
        });

        Schema::create('project_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('new_projects')->cascadeOnDelete();
            $table->string('unit_type', 50);
            $table->integer('carpet_area_sqft');
            $table->string('price_estimate', 60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_units');
        Schema::dropIfExists('new_projects');
    }
};

