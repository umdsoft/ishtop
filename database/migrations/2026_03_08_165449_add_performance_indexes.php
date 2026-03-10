<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->index('expires_at');
            $table->index('is_top');
            $table->index('is_urgent');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('expires_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('is_blocked');
        });

        // Make external_id unique to prevent double payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->index('external_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_blocked']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropIndex(['is_urgent']);
            $table->dropIndex(['is_top']);
            $table->dropIndex(['expires_at']);
        });
    }
};
