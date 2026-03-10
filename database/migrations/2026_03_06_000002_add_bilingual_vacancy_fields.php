<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = config('database.default') === 'sqlite';

        // Step 1: Drop FULLTEXT index (must be dropped before renaming columns it references)
        if (!$isSqlite) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->dropFullText('vacancies_title_description_fulltext');
            });
        }

        // Step 2: Rename existing columns to _uz variants
        Schema::table('vacancies', function (Blueprint $table) {
            $table->renameColumn('title', 'title_uz');
            $table->renameColumn('description', 'description_uz');
            $table->renameColumn('requirements', 'requirements_uz');
            $table->renameColumn('responsibilities', 'responsibilities_uz');
        });

        // Step 3: Add language column, _ru counterparts, and new FULLTEXT
        Schema::table('vacancies', function (Blueprint $table) use ($isSqlite) {
            $table->string('language', 5)->default('uz')->after('employer_id');

            $table->string('title_ru', 300)->nullable()->after('title_uz');
            $table->text('description_ru')->nullable()->after('description_uz');
            $table->text('requirements_ru')->nullable()->after('requirements_uz');
            $table->text('responsibilities_ru')->nullable()->after('responsibilities_uz');

            if (!$isSqlite) {
                $table->fullText(['title_uz', 'title_ru', 'description_uz', 'description_ru'], 'vacancies_bilingual_fulltext');
            }
        });
    }

    public function down(): void
    {
        $isSqlite = config('database.default') === 'sqlite';

        if (!$isSqlite) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->dropFullText('vacancies_bilingual_fulltext');
            });
        }

        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn(['title_ru', 'description_ru', 'requirements_ru', 'responsibilities_ru', 'language']);
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->renameColumn('title_uz', 'title');
            $table->renameColumn('description_uz', 'description');
            $table->renameColumn('requirements_uz', 'requirements');
            $table->renameColumn('responsibilities_uz', 'responsibilities');
        });

        if (!$isSqlite) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->fullText(['title', 'description']);
            });
        }
    }
};
