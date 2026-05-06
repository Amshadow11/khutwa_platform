<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    // ========================================================
    // صفحة "تحقق من بريدك الإلكتروني"
    // ========================================================

    /**
     * GET /email/verify
     *
     * تظهر للمستخدم بعد التسجيل مباشرةً.
     * إذا كان المستخدم متحققاً بالفعل → أعد توجيهه للـ Dashboard.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('user.dashboard'));
        }

        return view('auth.verify-email');
    }

    // ========================================================
    // معالجة رابط التحقق
    // ========================================================

    /**
     * GET /email/verify/{id}/{hash}
     *
     * يُستدعى عندما يضغط المستخدم الرابط في إيميله.
     *
     * EmailVerificationRequest يتحقق تلقائياً من:
     *   - أن الـ id مطابق للمستخدم المسجّل دخوله
     *   - أن الـ hash صحيح (HMAC موقَّع)
     *   - أن الـ URL لم يُعدَّل
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('user.dashboard')
                ->with('info', 'بريدك الإلكتروني متحقق منه بالفعل.');
        }

        // تسجيل وقت التحقق في email_verified_at
        $request->fulfill();

        return redirect()
            ->route('user.dashboard')
            ->with('success', 'تم تفعيل حسابك بنجاح! مرحباً بك في منصة خطوة.');
    }

    // ========================================================
    // إعادة إرسال إيميل التحقق
    // ========================================================

    /**
     * POST /email/verification-notification
     *
     * يُرسل إيميل تحقق جديد إذا لم يصل الأول أو انتهت صلاحيته.
     * محمي بـ throttle:3,1 في الـ Route — لا يمكن الإساءة.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('user.dashboard')
                ->with('info', 'بريدك الإلكتروني متحقق منه بالفعل.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'تم إرسال رابط التحقق مجدداً. تحقق من صندوق الوارد أو مجلد Spam.');
    }
}