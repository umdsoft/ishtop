<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vacancy_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_booked')->default(false);
            $table->foreignUuid('booked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vacancy_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_slots');
    }
};
