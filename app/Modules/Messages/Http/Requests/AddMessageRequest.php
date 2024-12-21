<?php

namespace App\Modules\Messages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'recipients' => ['sometimes', 'array'],
            'recipients.*' => ['exists:users,id']
        ];
    }
}
