<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employer_id')->constrained('employer_profiles')->cascadeOnDelete();
            $table->string('title', 300);
            $table->string('category', 50);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->string('salary_type', 20)->default('negotiable');
            $table->string('work_type', 20);
            $table->string('experience_required', 20)->default('no_experience');
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_method', 30)->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->string('status', 20)->default('draft');
            $table->boolean('is_top')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->timestamp('top_until')->nullable();
            $table->timestamp('urgent_until')->nullable();
            $table->boolean('has_questionnaire')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'city', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['latitude', 'longitude']);
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
