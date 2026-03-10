<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'sometimes|string|max:300',
            'title_ru' => 'nullable|string|max:300',
            'category' => 'sometimes|string|max:50',
            'description_uz' => 'sometimes|string',
            'description_ru' => 'nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
            'work_type' => 'sometimes|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'experience_required' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
            'status' => 'nullable|string|in:draft,paused,closed',
        ];
    }
}
