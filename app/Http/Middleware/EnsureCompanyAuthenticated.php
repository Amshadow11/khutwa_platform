<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAuthenticated
{
    /**
     * يتحقق أن المستخدم مسجل دخول كشركة.
     *
     * يستبدل هذا الكود المكرر في كل ملف PHP:
     *   if (!isset($_SESSION['company_id']) || $_SESSION['login_type'] !== 'company') {
     *       header('Location: login.php');
     *       exit();
     *   }
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('company')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير مصرح'], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'يرجى تسجيل الدخول أولاً');
        }

        $company = Auth::guard('company')->user();

        // التحقق من أن حساب الشركة نشط (ليس معلقاً أو محذوفاً)
        if ($company->status === 'inactive') {
            Auth::guard('company')->logout();

            return redirect()
                ->route('login')
                ->with('error', 'حسابك معطّل. يرجى التواصل مع الإدارة');
        }

        return $next($request);
    }
}
