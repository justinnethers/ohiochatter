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
        return [
            'title' => 'required',
            'body' => 'required',
            'forum_id' => 'required|exists:forums,id',
            'has_poll' => 'sometimes|boolean', // sometimes instead of required
            'poll_type' => 'required_if:has_poll,1|in:single,multiple',
            'options' => 'required_if:has_poll,1|array|min:2',
            'options.*' => 'string'
        ];
    }
}
