<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resume = $this->route('resume');

        return $resume && $this->user('web')?->can('update', $resume);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'template_id' => ['required', 'exists:resume_templates,id'],
            'visibility' => ['required', 'in:private,public,unlisted,signed'],
            'locale' => ['required', 'string', 'max:10'],
            'direction' => ['required', 'in:rtl,ltr'],
            'tailored_summary' => ['nullable', 'string', 'max:2500'],
            'sections' => ['nullable', 'array'],
            'sections.*.title' => ['nullable', 'string', 'max:160'],
            'sections.*.is_visible' => ['sometimes', 'boolean'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'sections.*.featured_only' => ['sometimes', 'boolean'],
            'sections.*.limit' => ['nullable', 'integer', 'min:0', 'max:50'],
        ];
    }
}
