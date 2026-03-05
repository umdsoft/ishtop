<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('worker_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('worker_user_id');
            $table->index('employer_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
