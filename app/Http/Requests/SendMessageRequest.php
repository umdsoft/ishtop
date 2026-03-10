<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'required_without:file_url|string|max:2000',
            'type' => 'nullable|in:text,file,image',
            'file_url' => 'nullable|string|max:500',
        ];
    }
}
