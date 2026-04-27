<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'new_password.required'     => 'كلمة المرور الجديدة مطلوبة',
            'new_password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'new_password.confirmed'    => 'كلمة المرور الجديدة وتأكيدها غير متطابقان',
        ];
    }
}
