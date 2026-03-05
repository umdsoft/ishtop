<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('sort_order');
            $table->string('value', 100);
            $table->string('label_uz', 300);
            $table->string('label_ru', 300)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->smallInteger('score_value')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
