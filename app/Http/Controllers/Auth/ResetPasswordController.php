<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    // ========================================================
    // عرض صفحة إدخال كلمة المرور الجديدة
    // ========================================================

    /**
     * GET /reset-password/{token}?email=...
     * GET /company/reset-password/{token}?email=...
     *
     * اللينك يصل من الإيميل ويحتوي token + email كـ query param.
     */
    public function showForm(Request $request, string $token, string $type = 'user'): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
            'type'  => $type,
        ]);
    }

    // ========================================================
    // تطبيق كلمة المرور الجديدة
    // ========================================================

    /**
     * POST /reset-password
     * POST /company/reset-password
     */
    public function reset(Request $request, string $type = 'user'): RedirectResponse
    {
        $request->validate(
            [
                'token'                 => ['required'],
                'email'                 => ['required', 'email'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ],
            [
                'token.required'                 => 'رابط إعادة التعيين غير صالح',
                'email.required'                 => 'البريد الإلكتروني مطلوب',
                'email.email'                    => 'البريد الإلكتروني غير صالح',
                'password.required'              => 'كلمة المرور الجديدة مطلوبة',
                'password.min'                   => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                'password.confirmed'             => 'كلمة المرور وتأكيدها غير متطابقان',
                'password_confirmation.required' => 'تأكيد كلمة المرور مطلوب',
            ]
        );

        $broker = $type === 'company' ? 'companies' : 'users';

        // Password::broker()->reset() يتحقق من:
        // 1. صحة الـ token
        // 2. أن الـ token لم ينتهِ (60 دقيقة)
        // 3. أن الإيميل مطابق للـ token
        // ثم يستدعي الـ callback لتحديث كلمة المرور
        $status = Password::broker($broker)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User|Company $user, string $password) {
                // تحديث كلمة المرور — الـ 'hashed' cast يُشفّرها تلقائياً
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // إطلاق Event لإلغاء جميع Sessions النشطة (أمان)
                event(new PasswordReset($user));
            }
        );

        // Password::PASSWORD_RESET    → تم التعيين بنجاح
        // Password::INVALID_TOKEN     → token منتهي أو خاطئ
        // Password::INVALID_USER      → الإيميل غير موجود
        // Password::RESET_THROTTLED   → طلبات كثيرة

        if ($status === Password::PASSWORD_RESET) {
            $loginRoute = $type === 'company' ? 'login' : 'login';

            return redirect()
                ->route($loginRoute)
                ->with('success', 'تم تعيين كلمة المرور الجديدة بنجاح. يمكنك الدخول الآن.');
        }

        if ($status === Password::INVALID_TOKEN) {
            return back()->withErrors([
                'token' => 'رابط إعادة التعيين غير صالح أو منتهي الصلاحية. اطلب رابطاً جديداً.',
            ]);
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}