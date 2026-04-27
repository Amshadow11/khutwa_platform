<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    // ========================================================
    // عرض صفحة تسجيل الدخول
    // ========================================================

    /**
     * GET /login
     */
    public function showForm(): View|RedirectResponse
    {
        // إذا كان مسجلاً دخوله بالفعل — تحويله للداشبورد المناسب
        if (Auth::guard('company')->check()) {
            return redirect()->route('company.dashboard');
        }
        if (Auth::guard('web')->check()) {
            return redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    // ========================================================
    // معالجة تسجيل الدخول
    // ========================================================

    /**
     * POST /login
     *
     * يعالج تسجيل دخول الشركات والمستخدمين من نموذج واحد.
     */
    public function login(Request $request): RedirectResponse
    {
        // --- 1. التحقق من المدخلات ---
        $request->validate([
            'email'      => ['required', 'email'],
            'password'   => ['required', 'string'],
            'login_type' => ['required', 'in:user,company'],
        ], [
            'email.required'      => 'البريد الإلكتروني مطلوب',
            'email.email'         => 'البريد الإلكتروني غير صالح',
            'password.required'   => 'كلمة المرور مطلوبة',
            'login_type.required' => 'يرجى تحديد نوع الحساب',
        ]);

        // --- 2. Rate Limiting — الحماية من Brute Force ---
        $this->checkRateLimit($request);

        $credentials = $request->only('email', 'password');
        $loginType   = $request->login_type;

        // --- 3. محاولة تسجيل الدخول حسب النوع ---
        if ($loginType === 'company') {
            return $this->loginAsCompany($request, $credentials);
        }

        return $this->loginAsUser($request, $credentials);
    }

    // ========================================================
    // تسجيل الخروج
    // ========================================================

    /**
     * POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        // تسجيل خروج من كلا الـ Guards
        Auth::guard('company')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح');
    }

    // ========================================================
    // Private Methods
    // ========================================================

    /**
     * تسجيل دخول الشركة عبر guard 'company'.
     */
    private function loginAsCompany(Request $request, array $credentials): RedirectResponse
    {
        if (! Auth::guard('company')->attempt($credentials, $request->boolean('remember'))) {
            $this->incrementRateLimit($request);

            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ]);
        }

        $company = Auth::guard('company')->user();

        // التحقق من حالة الحساب
        if ($company->status === 'inactive') {
            Auth::guard('company')->logout();
            throw ValidationException::withMessages([
                'email' => 'حسابك معطّل. يرجى التواصل مع الإدارة',
            ]);
        }

        // تحديث آخر تسجيل دخول
        $company->update(['last_login' => now()]);

        // تجديد الـ Session (منع Session Fixation)
        $request->session()->regenerate();
        $this->clearRateLimit($request);

        return redirect()
            ->intended(route('company.dashboard'))
            ->with('success', "مرحباً {$company->company_name}");
    }

    /**
     * تسجيل دخول المستخدم عبر guard 'web'.
     */
    private function loginAsUser(Request $request, array $credentials): RedirectResponse
    {
        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $this->incrementRateLimit($request);

            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ]);
        }

        $user = Auth::guard('web')->user();

        // التحقق من أن الحساب نشط
        if (! $user->is_active || $user->status === 'inactive') {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'حسابك غير مفعّل. يرجى التواصل مع الإدارة',
            ]);
        }

        // تحديث آخر تسجيل دخول
        $user->update(['last_login' => now()]);

        $request->session()->regenerate();
        $this->clearRateLimit($request);

        return redirect()
            ->intended(route('user.dashboard'))
            ->with('success', "مرحباً {$user->display_name}");
    }

    // ========================================================
    // Rate Limiting Helpers
    // ========================================================

    /**
     * مفتاح التعريف الفريد لكل محاولة دخول (IP + email).
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }

    /**
     * التحقق من تجاوز حد المحاولات (5 محاولات كل دقيقة).
     */
    private function checkRateLimit(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => "تم تجاوز عدد محاولات تسجيل الدخول. حاول مجدداً بعد {$seconds} ثانية",
            ]);
        }
    }

    private function incrementRateLimit(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 60);
    }

    private function clearRateLimit(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }
}
