<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyVerified
{
    /**
     * يتحقق أن الشركة مفعّلة ومعتمدة من الإدارة قبل السماح بنشر الوظائف.
     *
     * الشروط المطلوبة:
     *   1. is_verified = true  ← اعتمدها الأدمن
     *   2. status = 'active'   ← حسابها نشط
     *
     * يُطبَّق فقط على مسارات الكتابة (create/store/edit/update/destroy/toggle).
     * مسارات القراءة (index/show) لا تحتاجه.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = Auth::guard('company')->user();

        if (! $company->is_verified || $company->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'حسابك قيد المراجعة. لا يمكنك نشر الوظائف قبل اعتماد الحساب من الإدارة.',
                ], 403);
            }

            return redirect()
                ->route('company.dashboard')
                ->with('error', 'حسابك قيد المراجعة من الإدارة. لا يمكنك نشر الوظائف حتى يتم التفعيل.');
        }

        return $next($request);
    }
}