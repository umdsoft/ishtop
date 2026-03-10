<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vakansiyalar — employer bo'yicha tezkor qidiruv
        Schema::table('vacancies', function (Blueprint $table) {
            $table->index('employer_id', 'idx_vacancies_employer_id');
            $table->index('category', 'idx_vacancies_category');
            $table->index('city', 'idx_vacancies_city');
            $table->index('status', 'idx_vacancies_status');
            $table->index(['status', 'published_at'], 'idx_vacancies_status_published');
            $table->index('expires_at', 'idx_vacancies_expires_at');
        });

        // Arizalar — vacancy va worker bo'yicha
        Schema::table('applications', function (Blueprint $table) {
            $table->index('worker_id', 'idx_applications_worker_id');
            $table->index(['vacancy_id', 'stage'], 'idx_applications_vacancy_stage');
        });

        // Xabarlar — chat va o'qilmagan xabarlar
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['chat_id', 'is_read'], 'idx_messages_chat_unread');
        });

        // Obunalar — foydalanuvchi va muddat
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('user_id', 'idx_subscriptions_user_id');
            $table->index('expires_at', 'idx_subscriptions_expires_at');
        });

        // To'lovlar — tashqi ID bo'yicha qidiruv
        Schema::table('payments', function (Blueprint $table) {
            $table->index('external_id', 'idx_payments_external_id');
            $table->index(['user_id', 'status'], 'idx_payments_user_status');
        });

        // Foydalanuvchilar — telegram_id va yaratilgan vaqt
        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at', 'idx_users_created_at');
        });

        // Worker profillar — qidiruv holati va shahar
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->index('search_status', 'idx_worker_profiles_search_status');
            $table->index('city', 'idx_worker_profiles_city');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropIndex('idx_vacancies_employer_id');
            $table->dropIndex('idx_vacancies_category');
            $table->dropIndex('idx_vacancies_city');
            $table->dropIndex('idx_vacancies_status');
            $table->dropIndex('idx_vacancies_status_published');
            $table->dropIndex('idx_vacancies_expires_at');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('idx_applications_worker_id');
            $table->dropIndex('idx_applications_vacancy_stage');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_chat_unread');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_user_id');
            $table->dropIndex('idx_subscriptions_expires_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_external_id');
            $table->dropIndex('idx_payments_user_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_created_at');
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_worker_profiles_search_status');
            $table->dropIndex('idx_worker_profiles_city');
        });
    }
};
