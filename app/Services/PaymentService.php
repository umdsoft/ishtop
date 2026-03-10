<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function create(User $user, array $data): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'status' => PaymentStatus::PENDING,
            'payable_type' => $data['payable_type'] ?? null,
            'payable_id' => $data['payable_id'] ?? null,
            'meta' => $data['meta'] ?? null,
        ]);
    }

    public function complete(Payment $payment, ?string $externalId = null): void
    {
        DB::transaction(function () use ($payment, $externalId) {
            $payment->update([
                'status' => PaymentStatus::COMPLETED,
                'external_id' => $externalId,
            ]);

            $this->activateService($payment);
        });
    }

    public function fail(Payment $payment): void
    {
        $payment->update(['status' => PaymentStatus::FAILED]);
    }

    public function payWithBalance(User $user, Payment $payment): bool
    {
        return DB::transaction(function () use ($user, $payment) {
            // Pessimistic locking — race condition oldini olish
            $lockedUser = User::lockForUpdate()->find($user->id);

            if ($lockedUser->balance < $payment->amount) {
                return false;
            }

            $lockedUser->decrement('balance', $payment->amount);
            $this->complete($payment, 'balance_' . now()->timestamp);

            return true;
        });
    }

    public function topUpBalance(User $user, float $amount): void
    {
        $user->increment('balance', $amount);
    }

    private function activateService(Payment $payment): void
    {
        $user = $payment->user;

        match ($payment->type) {
            'balance_topup' => $this->topUpBalance($user, $payment->amount),
            'subscription' => $this->activateSubscription($payment),
            'vacancy_top' => $this->activateVacancyTop($payment),
            'vacancy_urgent' => $this->activateVacancyUrgent($payment),
            'vacancy_post' => $this->activateVacancyPost($payment),
            'candidate_unlock' => null,
            default => null,
        };
    }

    private function activateSubscription(Payment $payment): void
    {
        $meta = $payment->meta ?? [];
        $planValue = $meta['plan'] ?? 'business';
        $plan = SubscriptionPlan::tryFrom($planValue) ?? SubscriptionPlan::BUSINESS;

        Subscription::create([
            'user_id' => $payment->user_id,
            'plan' => $plan,
            'status' => 'active',
            'price' => $payment->amount,
            'limits' => ['max_vacancies' => $plan->maxVacancies()],
            'features' => [],
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    private function activateVacancyTop(Payment $payment): void
    {
        if ($payment->payable_type === Vacancy::class && $payment->payable_id) {
            Vacancy::where('id', $payment->payable_id)->update([
                'is_top' => true,
                'top_until' => now()->addDays(7),
            ]);
        }
    }

    private function activateVacancyUrgent(Payment $payment): void
    {
        if ($payment->payable_type === Vacancy::class && $payment->payable_id) {
            Vacancy::where('id', $payment->payable_id)->update([
                'is_urgent' => true,
                'urgent_until' => now()->addDays(3),
            ]);
        }
    }

    private function activateVacancyPost(Payment $payment): void
    {
        if ($payment->payable_type === Vacancy::class && $payment->payable_id) {
            $vacancy = Vacancy::find($payment->payable_id);
            if ($vacancy) {
                app(VacancyService::class)->approve($vacancy);
            }
        }
    }
}
