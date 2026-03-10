<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // CHECK constraints are not supported by SQLite ALTER TABLE
        if (config('database.default') === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE users ADD CONSTRAINT chk_balance_non_negative CHECK (balance >= 0)');
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE users DROP CHECK chk_balance_non_negative');
    }
};
