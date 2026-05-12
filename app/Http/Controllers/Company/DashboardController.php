<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(): View
    {
        $company = Auth::guard('company')->user();

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

        $recentApplications = Application::with(['user:id,username,full_name,profile_picture', 'job:id,title'])
            ->whereIn('job_id', $company->jobs()->pluck('id'))
            ->latest('applied_at')
            ->limit(8)
            ->get();

        $chartData = Application::selectRaw("DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereIn('job_id', $company->jobs()->pluck('id'))
            ->where('applied_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // إضافة ملخص الاشتراك للـ Dashboard
        $usageSummary = $this->subscriptionService->getUsageSummary($company);

        return view('company.dashboard', compact(
            'stats', 'recentApplications', 'chartData', 'company', 'usageSummary'
        ));
    }
}