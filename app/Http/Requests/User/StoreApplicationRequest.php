<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'cv'           => [
                'nullable',
                'file',
                'mimes:pdf',   // يتحقق من MIME الحقيقي
                'max:5120',    // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cv.mimes' => 'ملف السيرة الذاتية يجب أن يكون PDF',
            'cv.max'   => 'حجم الملف يجب أن يكون أقل من 5MB',
        ];
    }
}
