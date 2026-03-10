<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+998\d{9}$/', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('web.phone_format'),
        ];
    }
}
