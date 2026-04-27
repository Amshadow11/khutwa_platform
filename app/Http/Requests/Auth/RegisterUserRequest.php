<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الكل مسموح له بالتسجيل
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required', 'string',
                'min:3', 'max:100',
                'unique:users,username',
                'regex:/^[\p{Arabic}a-zA-Z0-9_\s]+$/u', // عربي أو إنجليزي أو أرقام
            ],
            'full_name' => [
                'nullable', 'string', 'max:150',
            ],
            'email' => [
                'required', 'email',
                'max:150',
                'unique:users,email',
            ],
            'password' => [
                'required', 'string',
                'min:8',
                'confirmed',   // يتطلب حقل password_confirmation
            ],
            'phone' => [
                'required', 'string',
                'regex:/^[0-9\+\-\s]{7,15}$/',
            ],
            'phone_code' => [
                'nullable', 'string', 'max:5',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'  => 'اسم المستخدم مطلوب',
            'username.min'       => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل',
            'username.unique'    => 'اسم المستخدم مستخدم بالفعل، اختر اسماً آخر',
            'username.regex'     => 'اسم المستخدم يحتوي على رموز غير مسموح بها',
            'email.required'     => 'البريد الإلكتروني مطلوب',
            'email.email'        => 'البريد الإلكتروني غير صالح',
            'email.unique'       => 'البريد الإلكتروني مسجّل بالفعل',
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.min'       => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور وتأكيدها غير متطابقان',
            'phone.required'     => 'رقم الهاتف مطلوب',
            'phone.regex'        => 'رقم الهاتف غير صالح',
        ];
    }
}
