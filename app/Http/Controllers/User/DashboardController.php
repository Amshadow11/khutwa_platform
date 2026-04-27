<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * لوحة تحكم باحث العمل.
     * GET /user/dashboard
     *
     * سيُكتمل في الخطوة التالية مع:
     * - قائمة الوظائف المتاحة
     * - طلبات التقديم للمستخدم
     * - الملف الشخصي
     */
    public function index(): View
    {
        $user = Auth::guard('web')->user();

        $recentApplications = Application::with(['job.company:id,company_name,logo'])
            ->where('user_id', $user->id)
            ->latest('applied_at')
            ->limit(5)
            ->get();

        $stats = [
            'total_applications'  => Application::where('user_id', $user->id)->count(),
            'pending'             => Application::where('user_id', $user->id)->where('status', 'pending')->count(),
            'accepted'            => Application::where('user_id', $user->id)->where('status', 'accepted')->count(),
        ];

        return view('user.dashboard', compact('user', 'recentApplications', 'stats'));
    }
}
