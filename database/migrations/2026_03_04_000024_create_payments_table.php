<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('status', 20)->default('pending');
            $table->string('external_id', 200)->nullable();
            $table->string('payable_type', 100)->nullable();
            $table->uuid('payable_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payable_type', 'payable_id']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
