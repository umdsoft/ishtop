<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\Recruiter;
use App\Http\Controllers\Webhook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website API (/api/web/...)
|--------------------------------------------------------------------------
*/

Route::prefix('web')->middleware('web.locale')->group(function () {
    Route::get('/home', [Api\WebApiController::class, 'home']);
    Route::get('/vacancies', [Api\WebApiController::class, 'vacancies']);
    Route::get('/vacancies/{id}', [Api\WebApiController::class, 'show'])->whereUuid('id');
    Route::post('/vacancies/{id}/apply', [Api\WebApiController::class, 'apply'])
        ->whereUuid('id')
        ->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Mini App API (/api/...)
|--------------------------------------------------------------------------
*/

// Auth
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/telegram', [Api\AuthController::class, 'telegram']);
    Route::post('/telegram-init', [Api\AuthController::class, 'telegramInit']);
    Route::post('/telegram-token', [Api\AuthController::class, 'telegramToken']);
    Route::post('/verify-phone', [Api\AuthController::class, 'verifyPhone']);
    Route::post('/verify-otp', [Api\AuthController::class, 'verifyOtp']);
});

// Public endpoints (no auth required)
Route::get('/vacancies', [Api\VacancyController::class, 'index'])->name('api.vacancies.index');
Route::get('/vacancies/{vacancy}', [Api\VacancyController::class, 'show'])
    ->name('api.vacancies.show')
    ->whereUuid('vacancy');
Route::get('/categories', [Api\SearchController::class, 'categories']);
Route::get('/cities', [Api\SearchController::class, 'cities']);
Route::get('/locations', [Api\LocationController::class, 'index']);

Route::middleware(['telegram.auth', 'throttle:api'])->group(function () {
    Route::get('/me', [Api\AuthController::class, 'me']);
    Route::put('/me', [Api\AuthController::class, 'updateProfile']);
    Route::post('/logout', [Api\AuthController::class, 'logout']);

    // Worker detail (public for employers viewing candidates)
    Route::get('/workers/{worker}', [Api\ProfileController::class, 'workerDetail'])->whereUuid('worker');

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/worker', [Api\ProfileController::class, 'workerShow']);
        Route::put('/worker', [Api\ProfileController::class, 'workerUpdate']);
        Route::put('/search-status', [Api\ProfileController::class, 'updateSearchStatus']);
        Route::get('/employer', [Api\ProfileController::class, 'employerShow']);
        Route::put('/employer', [Api\ProfileController::class, 'employerUpdate']);
        Route::get('/employer/resume', [Api\ProfileController::class, 'employerResumeShow']);
        Route::put('/employer/resume', [Api\ProfileController::class, 'employerResumeUpdate']);

        // LinkedIn PDF Import
        Route::post('/linkedin/parse-pdf', [Api\LinkedInImportController::class, 'parsePdf']);
        Route::post('/linkedin/apply-import', [Api\LinkedInImportController::class, 'applyImport']);
    });

    // Vacancies (authenticated)
    Route::get('vacancies/my', [Api\VacancyController::class, 'my']);
    Route::get('vacancies/pricing', [Api\VacancyController::class, 'pricing']);
    Route::get('vacancies/nearby', [Api\VacancyController::class, 'nearby']);
    Route::get('vacancies/recommended', [Api\VacancyController::class, 'recommended']);
    Route::post('vacancies/{vacancy}/activate', [Api\VacancyController::class, 'activate']);
    Route::get('vacancies/{vacancy}/candidates', [Api\VacancyController::class, 'candidates']);
    Route::post('vacancies/{vacancy}/unlock-candidates', [Api\VacancyController::class, 'unlockCandidates']);
    Route::post('vacancies', [Api\VacancyController::class, 'store'])
        ->name('api.vacancies.store')
        ->middleware('throttle:vacancy-create');
    Route::put('vacancies/{vacancy}', [Api\VacancyController::class, 'update'])->name('api.vacancies.update');
    Route::delete('vacancies/{vacancy}', [Api\VacancyController::class, 'destroy'])->name('api.vacancies.destroy');

    // Applications
    Route::post('/applications', [Api\ApplicationController::class, 'store'])
        ->middleware('throttle:application-submit');
    Route::get('/applications/my', [Api\ApplicationController::class, 'my']);
    Route::get('/applications/received', [Api\ApplicationController::class, 'received']);
    Route::put('/applications/{application}/stage', [Api\ApplicationController::class, 'updateStage']);
    Route::delete('/applications/{application}/withdraw', [Api\ApplicationController::class, 'withdraw']);

    // Questionnaire
    Route::get('/vacancies/{vacancy}/questionnaire', [Api\QuestionnaireController::class, 'show']);
    Route::post('/questionnaires/{questionnaire}/respond', [Api\QuestionnaireController::class, 'respond']);

    // Search
    Route::middleware('throttle:search')->group(function () {
        Route::get('/search/vacancies', [Api\SearchController::class, 'vacancies']);
        Route::get('/search/workers', [Api\SearchController::class, 'workers']);
        Route::get('/search/nearby', [Api\SearchController::class, 'nearby']);
    });

    // Chat
    Route::get('/chats', [Api\ChatController::class, 'index']);
    Route::get('/chats/{chat}/messages', [Api\ChatController::class, 'messages']);
    Route::post('/chats/{chat}/send', [Api\ChatController::class, 'send']);

    // Reviews
    Route::post('/reviews', [Api\ReviewController::class, 'store']);
    Route::get('/reviews/employer/{employer}', [Api\ReviewController::class, 'byEmployer']);

    // Payments
    Route::middleware('throttle:payment')->group(function () {
        Route::post('/payments/create', [Api\PaymentController::class, 'create']);
        Route::get('/payments/history', [Api\PaymentController::class, 'history']);
        Route::post('/payments/topup', [Api\PaymentController::class, 'topup']);
    });

    // Subscriptions
    Route::post('/subscriptions', [Api\SubscriptionController::class, 'store']);
    Route::get('/subscriptions/current', [Api\SubscriptionController::class, 'current']);
    Route::put('/subscriptions/{subscription}/cancel', [Api\SubscriptionController::class, 'cancel']);

    // Banners
    Route::get('/banners', [Api\BannerController::class, 'index']);
    Route::post('/banners/{banner}/impression', [Api\BannerController::class, 'impression']);
    Route::post('/banners/{banner}/click', [Api\BannerController::class, 'click']);

    // Saved
    Route::get('/saved', [Api\SavedController::class, 'index']);
    Route::post('/saved', [Api\SavedController::class, 'store']);
    Route::delete('/saved/{saved}', [Api\SavedController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [Api\NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [Api\NotificationController::class, 'markRead']);
    Route::put('/notifications/mark-all-read', [Api\NotificationController::class, 'markAllRead']);

});

/*
|--------------------------------------------------------------------------
| Recruiter API (/api/recruiter/...)
|--------------------------------------------------------------------------
*/

// Recruiter Auth (no auth required)
Route::prefix('recruiter')->middleware('throttle:recruiter')->group(function () {
    Route::post('/login', [Recruiter\AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [Recruiter\AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/send-otp', [Recruiter\AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/verify-otp', [Recruiter\AuthController::class, 'verifyOtp']);
    Route::get('/telegram-bot-info', [Recruiter\AuthController::class, 'telegramBotInfo']);
    Route::post('/telegram-login', [Recruiter\AuthController::class, 'telegramLogin']);
});

Route::prefix('recruiter')->middleware(['auth:sanctum', 'throttle:recruiter'])->group(function () {
    Route::get('/me', [Recruiter\AuthController::class, 'me']);

    // Company management
    Route::get('/companies', [Recruiter\CompanyController::class, 'index']);
    Route::post('/companies', [Recruiter\CompanyController::class, 'store']);
    Route::put('/companies/{company}', [Recruiter\CompanyController::class, 'update']);
    Route::delete('/companies/{company}', [Recruiter\CompanyController::class, 'destroy']);
    Route::post('/companies/{company}/switch', [Recruiter\CompanyController::class, 'switch']);

    Route::get('/dashboard', [Recruiter\DashboardController::class, 'index']);
    Route::get('/dashboard/recent-apps', [Recruiter\DashboardController::class, 'recentApplications']);
    Route::get('/dashboard/activity-chart', [Recruiter\DashboardController::class, 'activityChart']);

    Route::apiResource('/vacancies', Recruiter\VacancyController::class)->names('recruiter.vacancies');
    Route::post('/vacancies/translate', [Recruiter\VacancyController::class, 'translate'])->name('recruiter.vacancies.translate');
    Route::put('/vacancies/{vacancy}/toggle-status', [Recruiter\VacancyController::class, 'toggleStatus'])->name('recruiter.vacancies.toggle-status');

    // Questionnaire management
    Route::post('/vacancies/{vacancy}/questionnaire', [Recruiter\QuestionnaireController::class, 'store']);
    Route::get('/vacancies/{vacancy}/questionnaire', [Recruiter\QuestionnaireController::class, 'show']);
    Route::put('/questionnaires/{questionnaire}', [Recruiter\QuestionnaireController::class, 'update']);

    Route::post('/questions', [Recruiter\QuestionController::class, 'store']);
    Route::put('/questions/{question}', [Recruiter\QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [Recruiter\QuestionController::class, 'destroy']);
    Route::put('/questions/reorder', [Recruiter\QuestionController::class, 'reorder']);

    // Applications & Pipeline
    Route::get('/vacancies/{vacancy}/applications', [Recruiter\ApplicationController::class, 'index']);
    Route::get('/applications/{application}', [Recruiter\ApplicationController::class, 'show']);
    Route::put('/applications/{application}/stage', [Recruiter\PipelineController::class, 'updateStage']);
    Route::put('/applications/{application}/rate', [Recruiter\PipelineController::class, 'rate']);
    Route::post('/applications/{application}/note', [Recruiter\PipelineController::class, 'addNote']);
    Route::post('/applications/{application}/tags', [Recruiter\PipelineController::class, 'addTags']);

    Route::get('/applications/{application}/responses', [Recruiter\ResponseController::class, 'show']);
    Route::put('/answers/{answer}/manual-score', [Recruiter\ResponseController::class, 'manualScore']);

    Route::post('/compare', [Recruiter\CompareController::class, 'compare']);

    // Templates
    Route::get('/templates', [Recruiter\TemplateController::class, 'index']);
    Route::post('/templates', [Recruiter\TemplateController::class, 'store']);
    Route::put('/templates/{template}', [Recruiter\TemplateController::class, 'update']);
    Route::post('/templates/{template}/apply', [Recruiter\TemplateController::class, 'apply']);
    Route::delete('/templates/{template}', [Recruiter\TemplateController::class, 'destroy']);

    // Messages
    Route::get('/message-templates', [Recruiter\MessageTemplateController::class, 'index']);
    Route::post('/message-templates', [Recruiter\MessageTemplateController::class, 'store']);
    Route::post('/message-templates/{template}/send', [Recruiter\MessageTemplateController::class, 'send']);

    // Candidates (Nomzodlar)
    Route::get('/candidates/recommended', [Recruiter\CandidateController::class, 'recommended']);
    Route::get('/candidates/vacancies', [Recruiter\CandidateController::class, 'vacancies']);

    // Talent pool (saqlangan nomzodlar)
    Route::get('/talent-pool', [Recruiter\TalentPoolController::class, 'index']);
    Route::post('/talent-pool', [Recruiter\TalentPoolController::class, 'store']);
    Route::delete('/talent-pool/{entry}', [Recruiter\TalentPoolController::class, 'destroy']);

    // Analytics
    Route::get('/analytics/overview', [Recruiter\AnalyticsController::class, 'overview']);
    Route::get('/analytics/funnel', [Recruiter\AnalyticsController::class, 'funnel']);
    Route::get('/analytics/time-to-hire', [Recruiter\AnalyticsController::class, 'timeToHire']);
    Route::get('/analytics/questionnaire-stats', [Recruiter\AnalyticsController::class, 'questionnaireStats']);
});

/*
|--------------------------------------------------------------------------
| Admin API (/api/admin/...)
|--------------------------------------------------------------------------
*/

// Admin Auth (no auth required)
Route::prefix('admin')->group(function () {
    Route::post('/login', [Admin\AuthController::class, 'login']);
});

// Admin authenticated routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/me', [Admin\AuthController::class, 'me']);
    Route::post('/logout', [Admin\AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [Admin\DashboardController::class, 'stats']);
    Route::get('/dashboard/charts/{type}', [Admin\DashboardController::class, 'chart']);
    Route::get('/dashboard/pending-vacancies', [Admin\DashboardController::class, 'pendingVacancies']);
    Route::get('/dashboard/latest-vacancies', [Admin\DashboardController::class, 'latestVacancies']);

    // Users
    Route::get('users/stats', [Admin\UserController::class, 'stats']);
    Route::apiResource('users', Admin\UserController::class);
    Route::post('users/{user}/toggle-block', [Admin\UserController::class, 'toggleBlock']);

    // Workers
    Route::get('workers/regional-stats', [Admin\WorkerController::class, 'regionalStats']);
    Route::apiResource('workers', Admin\WorkerController::class)->only(['index', 'show']);

    // Employers
    Route::apiResource('employers', Admin\EmployerController::class)->only(['index', 'show', 'update']);

    // Vacancies
    Route::apiResource('vacancies', Admin\VacancyController::class)->except(['store']);
    Route::get('vacancies/{vacancy}/candidates', [Admin\VacancyController::class, 'candidates']);
    Route::post('vacancies/{vacancy}/approve', [Admin\VacancyController::class, 'approve']);
    Route::post('vacancies/{vacancy}/reject', [Admin\VacancyController::class, 'reject']);

    // Applications
    Route::apiResource('applications', Admin\ApplicationController::class)->only(['index', 'show']);

    // Categories
    Route::apiResource('categories', Admin\CategoryController::class);

    // Cities
    Route::apiResource('cities', Admin\CityController::class);

    // Payments
    Route::apiResource('payments', Admin\PaymentController::class)->only(['index', 'show']);

    // Subscriptions
    Route::apiResource('subscriptions', Admin\SubscriptionController::class)->only(['index', 'show', 'update']);

    // Banners
    Route::apiResource('banners', Admin\BannerController::class);

    // Reports
    Route::apiResource('reports', Admin\ReportController::class)->only(['index', 'show']);
    Route::post('reports/{report}/resolve', [Admin\ReportController::class, 'resolve']);
    Route::post('reports/{report}/dismiss', [Admin\ReportController::class, 'dismiss']);

    // Settings
    Route::get('settings', [Admin\SettingController::class, 'index']);
    Route::put('settings', [Admin\SettingController::class, 'update']);
    Route::post('settings/clear-cache', [Admin\SettingController::class, 'clearCache']);
});

/*
|--------------------------------------------------------------------------
| Payment Webhooks (no auth)
|--------------------------------------------------------------------------
*/

Route::prefix('payments/webhook')->middleware('throttle:webhook')->group(function () {
    Route::post('/payme', [Webhook\PaymeController::class, 'handle']);
    Route::post('/click/prepare', [Webhook\ClickController::class, 'prepare']);
    Route::post('/click/complete', [Webhook\ClickController::class, 'complete']);
    Route::post('/uzum', [Webhook\UzumController::class, 'handle']);
});

/*
|--------------------------------------------------------------------------
| Telegram Webhook
|--------------------------------------------------------------------------
*/

Route::post('/webhook/telegram/{secret?}', [Webhook\TelegramWebhookController::class, 'handle'])
    ->where('secret', '[a-zA-Z0-9_-]+');
