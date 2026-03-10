<?php

namespace Tests\Unit\Architecture;

use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class DryPrincipleTest extends TestCase
{
    // ── Form Request usage verification ──

    public function test_api_vacancy_store_uses_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\VacancyController::class,
            'store',
            \App\Http\Requests\StoreVacancyRequest::class
        );
    }

    public function test_api_vacancy_update_uses_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\VacancyController::class,
            'update',
            \App\Http\Requests\UpdateVacancyRequest::class
        );
    }

    public function test_recruiter_vacancy_store_uses_same_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\Recruiter\VacancyController::class,
            'store',
            \App\Http\Requests\StoreVacancyRequest::class
        );
    }

    public function test_recruiter_vacancy_update_uses_same_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\Recruiter\VacancyController::class,
            'update',
            \App\Http\Requests\UpdateVacancyRequest::class
        );
    }

    public function test_application_store_uses_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\ApplicationController::class,
            'store',
            \App\Http\Requests\StoreApplicationRequest::class
        );
    }

    public function test_review_store_uses_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\ReviewController::class,
            'store',
            \App\Http\Requests\StoreReviewRequest::class
        );
    }

    public function test_chat_send_uses_form_request(): void
    {
        $this->assertMethodUsesFormRequest(
            \App\Http\Controllers\Api\ChatController::class,
            'send',
            \App\Http\Requests\SendMessageRequest::class
        );
    }

    // ── Haversine centralization verification ──

    public function test_vacancy_scope_nearby_uses_geo_service(): void
    {
        $this->assertModelScopeUsesGeoService(\App\Models\Vacancy::class);
    }

    public function test_worker_profile_scope_nearby_uses_geo_service(): void
    {
        $this->assertModelScopeUsesGeoService(\App\Models\WorkerProfile::class);
    }

    // ── TelegramAuthService centralization ──

    public function test_auth_controller_does_not_have_verify_telegram_data_method(): void
    {
        $reflection = new ReflectionClass(\App\Http\Controllers\Api\AuthController::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertNotContains('verifyTelegramData', $methods,
            'AuthController should not have its own verifyTelegramData — use TelegramAuthService');
    }

    public function test_recruiter_auth_controller_does_not_have_verify_telegram_widget_method(): void
    {
        $reflection = new ReflectionClass(\App\Http\Controllers\Api\Recruiter\AuthController::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertNotContains('verifyTelegramWidget', $methods,
            'Recruiter AuthController should not have its own verifyTelegramWidget — use TelegramAuthService');
    }

    public function test_telegram_auth_service_exists(): void
    {
        $this->assertTrue(class_exists(\App\Services\TelegramAuthService::class));

        $reflection = new ReflectionClass(\App\Services\TelegramAuthService::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertContains('validateInitData', $methods);
        $this->assertContains('validateWidgetData', $methods);
    }

    // ── UserAuthResource DRY ──

    public function test_user_auth_resource_exists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Resources\UserAuthResource::class));
    }

    // ── Recruiter AuthController employer DRY ──

    public function test_recruiter_auth_has_ensure_employer_methods(): void
    {
        $reflection = new ReflectionClass(\App\Http\Controllers\Api\Recruiter\AuthController::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods(ReflectionMethod::IS_PRIVATE));

        $this->assertContains('ensureEmployerProfile', $methods);
        $this->assertContains('ensureActiveEmployer', $methods);
    }

    // ── Helper methods ──

    private function assertMethodUsesFormRequest(string $controller, string $method, string $formRequest): void
    {
        $reflection = new ReflectionMethod($controller, $method);
        $params = $reflection->getParameters();

        $found = false;
        foreach ($params as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin() && $type->getName() === $formRequest) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found,
            "{$controller}::{$method}() should use {$formRequest} as parameter");
    }

    private function assertModelScopeUsesGeoService(string $modelClass): void
    {
        $reflection = new ReflectionMethod($modelClass, 'scopeNearby');
        $source = file_get_contents($reflection->getFileName());

        // Extract just the scopeNearby method body
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $lines = array_slice(file($reflection->getFileName()), $startLine - 1, $endLine - $startLine + 1);
        $methodBody = implode('', $lines);

        $this->assertStringContainsString('GeoService::haversineFormula', $methodBody,
            "{$modelClass}::scopeNearby should use GeoService::haversineFormula() instead of hardcoded formula");
    }
}
