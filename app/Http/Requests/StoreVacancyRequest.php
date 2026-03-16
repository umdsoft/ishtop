<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'required_without:title_ru|nullable|string|max:300',
            'title_ru' => 'required_without:title_uz|nullable|string|max:300',
            'category' => 'required|string|max:50',
            'description_uz' => 'required_without:description_ru|nullable|string',
            'description_ru' => 'required_without:description_uz|nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
            'work_type' => 'required|string',
            'company_name' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0|gte:salary_min',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'currency' => 'nullable|string|in:uzs,usd',
            'experience_required' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
        ];
    }
}
