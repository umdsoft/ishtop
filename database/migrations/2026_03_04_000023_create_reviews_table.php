<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('worker_user_id')->constrained('users')->cascadeOnDelete();
            $table->smallInteger('rating');
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employer_profile_id');
            $table->unique(['employer_profile_id', 'worker_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
