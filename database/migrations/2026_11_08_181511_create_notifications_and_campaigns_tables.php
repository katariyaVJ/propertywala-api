<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('message');
            $table->string('category', 40)->default('system'); // 'lead', 'price_drop', 'payment', 'system'
            $table->string('reference_type', 40)->nullable(); // 'property', 'lead', 'pro_membership'
            $table->string('reference_id', 80)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
        });

        Schema::create('push_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('body');
            $table->string('category', 40)->default('System Alert');
            $table->string('target_audience', 60)->default('All Users');
            $table->string('target_city', 80)->nullable();
            $table->string('deep_link_url', 255)->nullable();
            $table->string('banner_image_url', 500)->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->string('status', 30)->default('Completed');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_campaigns');
        Schema::dropIfExists('notifications');
    }
};

