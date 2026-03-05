<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('saveable_type', 100);
            $table->uuid('saveable_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['saveable_type', 'saveable_id']);
            $table->unique(['user_id', 'saveable_type', 'saveable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_items');
    }
};
