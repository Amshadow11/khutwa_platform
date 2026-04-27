<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => [
                'required', 'string',
                'min:2', 'max:200',
                'unique:companies,company_name',
            ],
            'email' => [
                'required', 'email',
                'max:150',
                'unique:companies,email',
            ],
            'password' => [
                'required', 'string',
                'min:8',
                'confirmed',
            ],
            'phone' => [
                'required', 'string',
                'regex:/^[0-9\+\-\s]{7,15}$/',
            ],
            'phone_code' => [
                'nullable', 'string', 'max:5',
            ],
            'industry' => [
                'nullable', 'string', 'max:100',
            ],
            'company_size' => [
                'nullable',
                'in:startup,small,medium,large',
            ],
            'website' => [
                'nullable', 'url', 'max:255',
            ],
            'description' => [
                'nullable', 'string', 'max:2000',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'اسم الشركة مطلوب',
            'company_name.min'      => 'اسم الشركة قصير جداً',
            'company_name.unique'   => 'اسم الشركة مسجّل بالفعل',
            'email.required'        => 'البريد الإلكتروني مطلوب',
            'email.unique'          => 'البريد الإلكتروني مسجّل بالفعل',
            'password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed'    => 'كلمة المرور وتأكيدها غير متطابقان',
            'phone.required'        => 'رقم الهاتف مطلوب',
            'phone.regex'           => 'رقم الهاتف غير صالح',
            'website.url'           => 'رابط الموقع غير صالح (يجب أن يبدأ بـ https://)',
            'logo.image'            => 'الملف يجب أن يكون صورة',
            'logo.mimes'            => 'صيغ الصور المقبولة: JPG, PNG, WEBP',
            'logo.max'              => 'حجم الصورة يجب أن يكون أقل من 2MB',
        ];
    }
}
