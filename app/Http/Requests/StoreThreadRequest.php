<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required',
            'body' => 'required',
            'forum_id' => 'required|exists:forums,id',
            'has_poll' => 'nullable|boolean',
        ];

        if ($this->has_poll) {
            $rules['poll_type'] = 'required|in:single,multiple';
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string|max:255';
        }

        return $rules;
    }
}
