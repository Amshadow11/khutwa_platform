<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:220'],
            'issuing_organization' => ['nullable', 'string', 'max:220'],
            'credential_id' => ['nullable', 'string', 'max:180'],
            'credential_url' => ['nullable', 'url', 'max:500'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'does_not_expire' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'does_not_expire' => $this->boolean('does_not_expire'),
            'expires_at' => $this->boolean('does_not_expire') ? null : $this->input('expires_at'),
        ]);
    }
}
