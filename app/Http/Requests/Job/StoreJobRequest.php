<?php

namespace App\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
class StoreJobRequest extends FormRequest
{
    /**
     * هل المستخدم مصرح له بهذا الطلب؟
     * التحقق يحدث هنا — إذا أرجع false يُعطي 403 تلقائياً.
     */
    public function authorize(): bool
    {
        return Auth::guard('company')->check();
    }

    /**
     * قواعد التحقق.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'min:30',       // وصف قصير جداً لا فائدة منه
                'max:10000',
            ],
            'requirements' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'benefits' => [
                'nullable',
                'string',
                'max:3000',
            ],
            'category' => [
                'required',
                'in:job,training',
            ],
            'job_type' => [
                'required',
                'string',
                'in:full_time,part_time,remote,contract,freelance',
            ],
            'experience_level' => [
                'nullable',
                'in:junior,mid,senior,manager',
            ],
            'location' => [
                'required',
                'string',
                'max:150',
            ],
            'remote_work' => [
                'boolean',
            ],
            'salary' => [
                'nullable',
                'string',
                'max:100',
            ],
            'salary_range' => [
                'nullable',
                'string',
                'max:100',
            ],
            'deadline' => [
                'nullable',
                'date',
                'after:today',  // تاريخ الانتهاء يجب أن يكون في المستقبل
            ],
            'featured' => [
                'boolean',
            ],
            'urgent' => [
                'boolean',
            ],
        ];
    }

    /**
     * رسائل الخطأ بالعربية.
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'عنوان الوظيفة مطلوب',
            'title.min'            => 'عنوان الوظيفة يجب أن يكون 5 أحرف على الأقل',
            'title.max'            => 'عنوان الوظيفة طويل جداً (الحد الأقصى 255 حرف)',
            'description.required' => 'وصف الوظيفة مطلوب',
            'description.min'      => 'وصف الوظيفة قصير جداً (30 حرف على الأقل)',
            'category.required'    => 'نوع الإعلان مطلوب (وظيفة أو تدريب)',
            'category.in'          => 'نوع الإعلان غير صالح',
            'job_type.required'    => 'نوع الدوام مطلوب',
            'job_type.in'          => 'نوع الدوام غير صالح',
            'location.required'    => 'موقع الوظيفة مطلوب',
            'deadline.after'       => 'تاريخ انتهاء التقديم يجب أن يكون في المستقبل',
        ];
    }

    /**
     * معالجة البيانات قبل التحقق (Pre-processing).
     * نحوّل القيم المنطقية بشكل صحيح.
     */
    protected function prepareForValidation(): void
    {
        $company = Auth::guard('company')->user();

        // فحص الاشتراك — isSubscriptionActive() موجودة في Company Model
        $isPaid = $company?->isSubscriptionActive()  ?? false;

        $this->merge([
            'remote_work' => $this->boolean('remote_work'),
            'featured'    => $isPaid ? $this->boolean('featured') : false,
            'urgent'      => $isPaid ? $this->boolean('urgent')   : false,
        ]);
    }
}
