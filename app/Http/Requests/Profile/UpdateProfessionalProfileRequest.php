<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfessionalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:180'],
            'current_title' => ['nullable', 'string', 'max:180'],
            'current_company' => ['nullable', 'string', 'max:180'],
            'industry' => ['nullable', 'string', 'max:120'],
            'seniority_level' => ['nullable', 'string', 'max:60'],
            'location_country' => ['nullable', 'string', 'size:2'],
            'location_city' => ['nullable', 'string', 'max:120'],
            'open_to_work' => ['sometimes', 'boolean'],
            'profile_visibility' => ['required', 'in:public,private'],
            'public_sections' => ['nullable', 'array'],
            'public_sections.*' => ['string', 'in:about,skills,experience,education,projects,certifications,languages,links'],
            'preferred_job_types' => ['nullable', 'array'],
            'preferred_job_types.*' => ['string', 'max:60'],
            'preferred_locations' => ['nullable', 'array'],
            'preferred_locations.*' => ['string', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'open_to_work' => $this->boolean('open_to_work'),
            'public_sections' => $this->input('public_sections', []),
        ]);
    }
}
