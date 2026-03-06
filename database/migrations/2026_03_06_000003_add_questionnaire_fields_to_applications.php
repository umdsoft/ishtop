<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->decimal('questionnaire_max_score', 5, 2)->nullable()->after('questionnaire_score');
            $table->json('questionnaire_answers')->nullable()->after('questionnaire_max_score');
            $table->boolean('questionnaire_completed')->default(false)->after('questionnaire_answers');
            $table->timestamp('questionnaire_completed_at')->nullable()->after('questionnaire_completed');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'questionnaire_max_score',
                'questionnaire_answers',
                'questionnaire_completed',
                'questionnaire_completed_at',
            ]);
        });
    }
};
