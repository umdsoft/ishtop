<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 5, 2)->default(0);
            $table->boolean('knockout_passed')->default(true);
            $table->string('status', 20)->default('in_progress');
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();

            $table->index(['questionnaire_id', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_responses');
    }
};
