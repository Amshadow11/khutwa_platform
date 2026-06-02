<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'language_id' => ['required', Rule::exists('languages', 'id')->where('is_active', true)],
            'proficiency_level' => ['nullable', 'string', 'max:40'],
            'proficiency_score' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_native' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_native' => $this->boolean('is_native')]);
    }
}
