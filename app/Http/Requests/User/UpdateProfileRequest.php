<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check();
    }

    public function rules(): array
    {
        $userId = Auth::guard('web')->id();

        return [
            'username'        => ['required', 'string', 'min:3', 'max:100',
                                  Rule::unique('users', 'username')->ignore($userId)],
            'full_name'       => ['nullable', 'string', 'max:150'],
            'phone'           => ['required', 'string', 'regex:/^[0-9\+\-\s]{7,15}$/'],
            'address'         => ['nullable', 'string', 'max:255'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
            'gender'          => ['nullable', 'in:male,female'],
            'linkedin_url'    => ['nullable', 'url', 'max:255'],
            'github_url'      => ['nullable', 'url', 'max:255'],
            'portfolio_url'   => ['nullable', 'url', 'max:255'],
            'skills'          => ['nullable', 'string', 'max:2000'],
            'experience'      => ['nullable', 'string', 'max:3000'],
            'education'       => ['nullable', 'string', 'max:2000'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'        => 'اسم المستخدم مطلوب',
            'username.unique'          => 'اسم المستخدم مستخدم بالفعل',
            'phone.required'           => 'رقم الهاتف مطلوب',
            'phone.regex'              => 'رقم الهاتف غير صالح',
            'birth_date.before'        => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'profile_picture.mimes'    => 'صيغ الصور المقبولة: JPG, PNG, WEBP',
            'profile_picture.max'      => 'حجم الصورة يجب أن يكون أقل من 2MB',
        ];
    }
}
