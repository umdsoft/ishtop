<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 200);
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('education_level', 30)->nullable();
            $table->string('specialty', 200)->nullable();
            $table->smallInteger('experience_years')->default(0);
            $table->json('skills')->nullable();
            $table->integer('expected_salary_min')->nullable();
            $table->integer('expected_salary_max')->nullable();
            $table->json('work_types')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->string('resume_file_url', 500)->nullable();
            $table->string('search_status', 20)->default('open');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'search_status']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
