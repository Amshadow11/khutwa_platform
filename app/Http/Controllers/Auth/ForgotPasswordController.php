<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    // ========================================================
    // عرض صفحة "نسيت كلمة المرور"
    // ========================================================

    /**
     * GET /forgot-password
     * GET /company/forgot-password
     *
     * نفس الـ view لكلا النوعين — نمرر $type لتحديد الـ broker.
     */
    public function showForm(string $type = 'user'): View
    {
        return view('auth.forgot-password', ['type' => $type]);
    }

    // ========================================================
    // إرسال رابط إعادة التعيين
    // ========================================================

    /**
     * POST /forgot-password
     * POST /company/forgot-password
     */
    public function sendLink(Request $request, string $type = 'user'): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'البريد الإلكتروني مطلوب',
             'email.email'    => 'البريد الإلكتروني غير صالح']
        );

        // اختيار الـ broker المناسب حسب نوع المستخدم
        // 'users'     → Password::broker('users')     → جدول users
        // 'companies' → Password::broker('companies') → جدول companies
        $broker = $type === 'company' ? 'companies' : 'users';

        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        // Password::RESET_LINK_SENT  → تم الإرسال بنجاح
        // Password::RESET_THROTTLED  → طلبات كثيرة — انتظر
        // Password::INVALID_USER     → الإيميل غير موجود
        //
        // ⚠️ نُرجع نفس رسالة النجاح حتى في حالة INVALID_USER
        // لمنع User Enumeration Attack (معرفة أي إيميلات مسجّلة)
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success',
                'إذا كان البريد الإلكتروني مسجلاً، ستصلك رسالة خلال دقائق.'
            );
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' =>
                'طلبت رابطاً مؤخراً. انتظر دقيقة قبل المحاولة مجدداً.'
            ]);
        }

        // INVALID_USER أو أي خطأ آخر — نُرجع نفس رسالة النجاح (أمان)
        return back()->with('success',
            'إذا كان البريد الإلكتروني مسجلاً، ستصلك رسالة خلال دقائق.'
        );
    }
}