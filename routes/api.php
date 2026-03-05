<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Recruiter;
use App\Http\Controllers\Webhook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mini App API (/api/...)
|--------------------------------------------------------------------------
*/

// Auth
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/telegram', [Api\AuthController::class, 'telegram']);
    Route::post('/verify-phone', [Api\AuthController::class, 'verifyPhone']);
    Route::post('/verify-otp', [Api\AuthController::class, 'verifyOtp']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/me', [Api\AuthController::class, 'me']);
    Route::put('/me', [Api\AuthController::class, 'updateProfile']);
    Route::post('/logout', [Api\AuthController::class, 'logout']);

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/worker', [Api\ProfileController::class, 'workerShow']);
        Route::put('/worker', [Api\ProfileController::class, 'workerUpdate']);
        Route::put('/search-status', [Api\ProfileController::class, 'updateSearchStatus']);
        Route::get('/employer', [Api\ProfileController::class, 'employerShow']);
        Route::put('/employer', [Api\ProfileController::class, 'employerUpdate']);
        Route::get('/employer/resume', [Api\ProfileController::class, 'employerResumeShow']);
        Route::put('/employer/resume', [Api\ProfileController::class, 'employerResumeUpdate']);
    });

    // Vacancies
    Route::apiResource('vacancies', Api\VacancyController::class)
        ->names('api.vacancies')
        ->except(['store']);
    Route::post('vacancies', [Api\VacancyController::class, 'store'])
        ->name('api.vacancies.store')
        ->middleware('throttle:vacancy-create');
    Route::get('/vacancies/nearby', [Api\VacancyController::class, 'nearby']);
    Route::get('/vacancies/recommended', [Api\VacancyController::class, 'recommended']);

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

    // Categories & Cities
    Route::get('/categories', [Api\SearchController::class, 'categories']);
    Route::get('/cities', [Api\SearchController::class, 'cities']);
});

/*
|--------------------------------------------------------------------------
| Recruiter API (/api/recruiter/...)
|--------------------------------------------------------------------------
*/

// Recruiter Auth (no auth required)
Route::prefix('recruiter')->middleware('throttle:recruiter')->group(function () {
    Route::post('/login', [Recruiter\AuthController::class, 'login']);
    Route::post('/register', [Recruiter\AuthController::class, 'register']);
    Route::post('/send-otp', [Recruiter\AuthController::class, 'sendOtp']);
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
    Route::post('/templates/{template}/apply', [Recruiter\TemplateController::class, 'apply']);

    // Messages
    Route::get('/message-templates', [Recruiter\MessageTemplateController::class, 'index']);
    Route::post('/message-templates', [Recruiter\MessageTemplateController::class, 'store']);
    Route::post('/message-templates/{template}/send', [Recruiter\MessageTemplateController::class, 'send']);

    // Talent pool
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
| Payment Webhooks (no auth)
|--------------------------------------------------------------------------
*/

Route::prefix('payments/webhook')->middleware('throttle:webhook')->group(function () {
    Route::post('/payme', [Webhook\PaymeController::class, 'handle']);
    Route::post('/click/prepare', [Webhook\ClickController::class, 'prepare']);
    Route::post('/click/complete', [Webhook\ClickController::class, 'complete']);
    Route::post('/uzum', [Webhook\UzumController::class, 'handle']);
});
