<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove UNIQUE constraint from employer_profiles.user_id
        //    Must drop FK first, then unique index, then re-add FK (MariaDB requirement)
        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // 2. Add active_employer_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('active_employer_id')
                ->nullable()
                ->after('balance')
                ->constrained('employer_profiles')
                ->nullOnDelete();
        });

        // 3. Back-fill: set active_employer_id for existing users
        DB::statement('
            UPDATE users
            SET active_employer_id = (
                SELECT id FROM employer_profiles
                WHERE employer_profiles.user_id = users.id
                AND employer_profiles.deleted_at IS NULL
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM employer_profiles
                WHERE employer_profiles.user_id = users.id
                AND employer_profiles.deleted_at IS NULL
            )
        ');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_employer_id');
        });

        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->foreignUuid('user_id')->unique()->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
