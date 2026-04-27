<?php

namespace App\Http\Requests\Job;

class UpdateJobRequest extends StoreJobRequest
{
    /**
     * عند التحديث — جميع الحقول اختيارية (ما لم تُرسَل لا تُتحقق).
     * 'sometimes' تعني: "حقق فقط إذا كان الحقل موجوداً في الطلب".
     */
    public function rules(): array
    {
        // نأخذ قواعد الإنشاء ونضيف 'sometimes' لكل حقل
        return collect(parent::rules())
            ->map(fn($rules) => array_merge(['sometimes'], (array) $rules))
            ->toArray();
    }
}
