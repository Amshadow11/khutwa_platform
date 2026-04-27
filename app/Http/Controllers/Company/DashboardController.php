<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * لوحة تحكم الشركة الرئيسية.
     * GET /company/dashboard
     *
     * يُحل مشكلة الـ 5 Correlated Subqueries الموجودة في company_dashboard.php
     */
    public function index(): View
    {
        $company = Auth::guard('company')->user();

        // --------------------------------------------------------
        // الإحصائيات — مُخزَّنة مؤقتاً لـ 5 دقائق
        // يمنع تكرار الاستعلامات في كل تحميل للصفحة
        // --------------------------------------------------------
        $stats = Cache::remember("company_dashboard_{$company->id}", 300, function () use ($company) {
            $jobIds = $company->jobs()->pluck('id');

            return [
                'total_jobs'    => $company->jobs()->count(),
                'active_jobs'   => $company->jobs()->active()->count(),
                'total_apps'    => Application::whereIn('job_id', $jobIds)->count(),
                'pending_apps'  => Application::whereIn('job_id', $jobIds)->where('status', 'pending')->count(),
                'accepted_apps' => Application::whereIn('job_id', $jobIds)->where('status', 'accepted')->count(),
            ];
        });

        // --------------------------------------------------------
        // آخر 8 طلبات واردة — مع Eager Loading
        // --------------------------------------------------------
        $recentApplications = Application::with(['user:id,username,full_name,profile_picture', 'job:id,title'])
            ->whereIn('job_id', $company->jobs()->pluck('id'))
            ->latest('applied_at')
            ->limit(8)
            ->get();

        // --------------------------------------------------------
        // بيانات الرسم البياني — آخر 6 أشهر
        // --------------------------------------------------------
        $chartData = Application::selectRaw("DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereIn('job_id', $company->jobs()->pluck('id'))
            ->where('applied_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return view('company.dashboard', compact('stats', 'recentApplications', 'chartData', 'company'));
    }
}
