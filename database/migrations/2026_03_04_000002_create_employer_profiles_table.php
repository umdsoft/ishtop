<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name', 300);
            $table->string('industry', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website', 300)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->string('employees_count', 20)->nullable();
            $table->string('stir_number', 20)->nullable();
            $table->string('verification_level', 20)->default('new');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('response_time_avg')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('verification_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_profiles');
    }
};
