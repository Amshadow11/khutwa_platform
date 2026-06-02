<?php

namespace App\Http\Requests\ATS;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company')?->can('view', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:10', 'max:480'],
            'location_type' => ['required', 'in:online,onsite,phone'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
