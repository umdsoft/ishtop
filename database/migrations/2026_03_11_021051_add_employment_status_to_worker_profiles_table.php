<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->string('employment_status', 30)->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn('employment_status');
        });
    }
};
