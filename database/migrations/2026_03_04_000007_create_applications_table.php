<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('worker_id')->constrained('worker_profiles')->cascadeOnDelete();
            $table->string('stage', 30)->default('new');
            $table->text('cover_letter')->nullable();
            $table->decimal('questionnaire_score', 5, 2)->nullable();
            $table->boolean('knockout_passed')->default(true);
            $table->smallInteger('recruiter_rating')->nullable();
            $table->decimal('matching_score', 5, 2)->nullable();
            $table->string('source', 20)->default('organic');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('shortlisted_at')->nullable();
            $table->timestamp('interviewed_at')->nullable();
            $table->timestamp('offered_at')->nullable();
            $table->string('rejected_reason', 300)->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'worker_id']);
            $table->index(['vacancy_id', 'stage']);
            $table->index('worker_id');
            $table->index(['vacancy_id', 'questionnaire_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
