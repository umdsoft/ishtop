<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'telegram_id' => $this->telegram_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'language' => $this->language,
            'is_verified' => $this->is_verified,
            'has_worker_profile' => $this->relationLoaded('workerProfile')
                ? $this->workerProfile !== null
                : $this->workerProfile()->exists(),
            'has_employer_profile' => $this->employerProfile !== null,
            'employer_profile' => $this->employerProfile ? [
                'id' => $this->employerProfile->id,
                'company_name' => $this->employerProfile->company_name,
            ] : null,
            'balance' => $this->balance,
            'worker_profile' => $this->whenLoaded('workerProfile', function () {
                $wp = $this->workerProfile;
                return $wp ? [
                    'full_name' => $wp->full_name,
                    'city' => $wp->city,
                    'specialty' => $wp->specialty,
                    'expected_salary_min' => $wp->expected_salary_min,
                    'expected_salary_max' => $wp->expected_salary_max,
                    'work_types' => $wp->work_types,
                    'preferred_categories' => $wp->preferred_categories,
                ] : null;
            }),
        ];
    }
}
