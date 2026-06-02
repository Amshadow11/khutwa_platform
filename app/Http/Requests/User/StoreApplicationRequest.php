<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'cover_letter' => ['nullable', 'string', 'max:3000'],
            'resume_id' => [
                'nullable',
                'integer',
                'prohibits:cv',
                Rule::exists('resumes', 'id')->where(fn ($query) => $query
                    ->where('user_id', Auth::guard('web')->id())
                    ->whereNull('deleted_at')),
            ],
            'cv' => [
                'nullable',
                'file',
                'prohibits:resume_id',
                'mimes:pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cv.mimes' => 'ملف السيرة الذاتية يجب أن يكون PDF.',
            'cv.max' => 'حجم الملف يجب أن يكون أقل من 5MB.',
            'resume_id.prohibits' => 'اختر سيرة محفوظة أو ارفع PDF، لا تستخدم الخيارين معًا.',
            'cv.prohibits' => 'اختر سيرة محفوظة أو ارفع PDF، لا تستخدم الخيارين معًا.',
        ];
    }
}
