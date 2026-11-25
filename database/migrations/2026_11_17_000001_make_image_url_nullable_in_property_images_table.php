<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_images', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->default('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80')->change();
        });
    }

    public function down(): void
    {
        Schema::table('property_images', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable(false)->change();
        });
    }
};
