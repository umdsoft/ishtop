<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add category_id column (nullable initially for data migration)
        Schema::table('vacancies', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('category');
        });

        // Step 2: Populate category_id from existing category slug
        DB::table('vacancies')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->update([
                'category_id' => DB::raw(
                    '(SELECT id FROM categories WHERE slug = vacancies.category LIMIT 1)'
                ),
            ]);

        // Step 3: Add FK constraint and index
        // Separate Schema::table call because column must exist first
        Schema::table('vacancies', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
