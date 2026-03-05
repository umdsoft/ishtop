<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 300);
            $table->string('type', 30);
            $table->string('image_url', 500);
            $table->string('image_mobile_url', 500)->nullable();
            $table->string('click_url', 500)->nullable();
            $table->string('click_action', 30)->nullable();
            $table->string('advertiser_name', 200)->nullable();
            $table->string('advertiser_contact', 200)->nullable();
            $table->json('placement');
            $table->json('categories')->nullable();
            $table->json('cities')->nullable();
            $table->integer('priority')->default(0);
            $table->integer('max_impressions')->nullable();
            $table->integer('max_clicks')->nullable();
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->string('cost_type', 20)->nullable();
            $table->decimal('cost_amount', 12, 2)->nullable();
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
