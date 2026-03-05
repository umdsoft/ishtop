<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('sort_order');
            $table->string('type', 20);
            $table->text('text_uz');
            $table->text('text_ru')->nullable();
            $table->boolean('is_required')->default(true);
            $table->smallInteger('weight')->default(0);
            $table->boolean('is_knockout')->default(false);
            $table->json('correct_answer')->nullable();
            $table->json('scoring_config')->nullable();
            $table->timestamps();

            $table->index(['questionnaire_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
