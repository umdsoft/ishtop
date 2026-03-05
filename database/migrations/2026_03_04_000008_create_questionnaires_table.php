<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vacancy_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->smallInteger('time_limit_minutes')->nullable();
            $table->smallInteger('passing_score')->nullable();
            $table->smallInteger('auto_reject_below')->nullable();
            $table->boolean('is_template')->default(false);
            $table->string('template_name', 200)->nullable();
            $table->smallInteger('questions_count')->default(0);
            $table->integer('responses_count')->default(0);
            $table->decimal('avg_score', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
