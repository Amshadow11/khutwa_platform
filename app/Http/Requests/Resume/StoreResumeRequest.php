<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web')?->can('create', \App\Models\Resume::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'template_slug' => ['required', Rule::exists('resume_templates', 'slug')->where('is_active', true)],
            'visibility' => ['required', 'in:private,public,unlisted,signed'],
            'locale' => ['required', 'string', 'max:10'],
            'direction' => ['required', 'in:rtl,ltr'],
            'tailored_summary' => ['nullable', 'string', 'max:2500'],
            'target_job_id' => ['nullable', 'exists:jobs,id'],
        ];
    }
}
