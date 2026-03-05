<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('response_id')->constrained('questionnaire_responses')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained()->cascadeOnDelete();
            $table->json('answer_value');
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_knockout_failed')->default(false);
            $table->smallInteger('manual_score')->nullable();
            $table->uuid('manual_scored_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('response_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_answers');
    }
};
