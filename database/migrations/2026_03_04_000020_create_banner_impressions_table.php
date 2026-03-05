<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_impressions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('banner_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->string('placement', 30);
            $table->string('action', 20)->default('impression');
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['banner_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_impressions');
    }
};
