<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tag_id')->constrained('recruiter_tags')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['application_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_tags');
    }
};
