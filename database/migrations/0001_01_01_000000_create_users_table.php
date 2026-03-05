<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('telegram_id')->unique();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('language', 2)->default('uz');
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->string('referral_code', 20)->unique();
            $table->uuid('referred_by')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('referred_by');
        });

        // Laravel default tables needed
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
