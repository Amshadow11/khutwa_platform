<?php

namespace App\Http\Requests\ATS;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company')?->can('transitionStatus', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Application::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
