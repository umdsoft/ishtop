<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_pool_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recruiter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('worker_profile_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();

            $table->unique(['recruiter_user_id', 'worker_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_pool_entries');
    }
};
