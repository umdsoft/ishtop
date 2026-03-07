<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->string('linkedin_url', 500)->nullable()->after('resume_file_url');
            $table->json('linkedin_import_data')->nullable()->after('linkedin_url');
            $table->timestamp('linkedin_imported_at')->nullable()->after('linkedin_import_data');
        });
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn(['linkedin_url', 'linkedin_import_data', 'linkedin_imported_at']);
        });
    }
};
