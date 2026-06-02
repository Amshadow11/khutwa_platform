<?php

namespace App\Http\Requests\ATS;

use Illuminate\Foundation\Http\FormRequest;

class ReviewApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company')?->can('view', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'recommendation' => ['nullable', 'in:strong_yes,yes,maybe,no,strong_no'],
            'rubric_scores' => ['nullable', 'array'],
            'rubric_scores.technical_fit' => ['nullable', 'integer', 'min:1', 'max:5'],
            'rubric_scores.experience_fit' => ['nullable', 'integer', 'min:1', 'max:5'],
            'rubric_scores.role_fit' => ['nullable', 'integer', 'min:1', 'max:5'],
            'rubric_scores.communication' => ['nullable', 'integer', 'min:1', 'max:5'],
            'strengths' => ['nullable', 'string', 'max:3000'],
            'concerns' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
