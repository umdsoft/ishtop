<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_resumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employer_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('owner_name', 200);
            $table->string('position', 200)->nullable();
            $table->smallInteger('experience_years')->default(0);
            $table->string('education', 300)->nullable();
            $table->json('achievements')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_resumes');
    }
};
