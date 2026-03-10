<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vacancy_id' => 'required|uuid|exists:vacancies,id',
            'cover_letter' => 'nullable|string|max:2000',
        ];
    }
}
