<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    // ========================================================
    // صفحة التسجيل
    // ========================================================

    /**
     * GET /register
     */
    public function showForm(): View|RedirectResponse
    {
        if (Auth::guard('company')->check()) {
            return redirect()->route('company.dashboard');
        }
        if (Auth::guard('web')->check()) {
            return redirect()->route('user.dashboard');
        }

        return view('auth.register');
    }

    // ========================================================
    // تسجيل مستخدم جديد (باحث عمل)
    // ========================================================

    /**
     * POST /register/user
     *
     * Validation يحدث في RegisterUserRequest قبل دخول الدالة.
     */
    public function registerUser(RegisterUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'username'   => $request->username,
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'password'   => $request->password, // يُشفَّر تلقائياً بـ 'hashed' cast
            'phone'      => $request->phone,
            'phone_code' => $request->phone_code ?? 'YE',
            'status'     => 'active',
            'is_active'  => true,
        ]);

        // تسجيل دخول مباشر بعد التسجيل
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        event(new Registered($user));

        // توجيه لصفحة "تحقق من بريدك الإلكتروني"
        // المستخدم مسجّل دخول لكن لم يتحقق بعد
        return redirect()
            ->route('verification.notice')
            ->with('success', "مرحباً {$user->display_name}! تم إنشاء حسابك. تحقق من بريدك الإلكتروني لتفعيل الحساب.");
    }
    // ========================================================
    // تسجيل شركة جديدة
    // ========================================================

    /**
     * POST /register/company
     */
    public function registerCompany(RegisterCompanyRequest $request): RedirectResponse
    {
        $company = Company::create([
            'company_name'     => $request->company_name,
            'email'            => $request->email,
            'password'         => $request->password,
            'phone'            => $request->phone,
            'phone_code'       => $request->phone_code ?? 'YE',
            'industry'         => $request->industry,
            'company_size'     => $request->company_size ?? 'small',
            'website'          => $request->website,
            'description'      => $request->description,
            'status'           => 'pending',   // تنتظر موافقة الإدارة
            'is_verified'      => false,
            'subscription_plan'=> 'free',
        ]);

        // تسجيل دخول مباشر
        Auth::guard('company')->login($company);
        $request->session()->regenerate();

        return redirect()
            ->route('company.dashboard')
            ->with('success', "مرحباً {$company->company_name}! حسابك قيد المراجعة من الإدارة");
    }
}
