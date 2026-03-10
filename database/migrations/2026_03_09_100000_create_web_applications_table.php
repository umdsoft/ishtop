<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vacancy_id')->constrained('vacancies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->text('message')->nullable();
            $table->string('ip_address', 45);
            $table->timestamps();

            $table->index(['vacancy_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_applications');
    }
};
