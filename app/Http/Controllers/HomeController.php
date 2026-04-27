<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // إحصائيات الموقع — Cache 10 دقائق
        $stats = Cache::remember('home_stats', 600, function () {
            return [
                'users'        => User::where('is_active', true)->count(),
                'companies'    => Company::where('is_verified', true)->count(),
                'jobs'         => Job::active()->count(),
                'applications' => Application::count(),
            ];
        });

        // أحدث الوظائف — Cache 5 دقائق
        $latestJobs = Cache::remember('home_latest_jobs', 300, function () {
            return Job::with('company:id,company_name,logo')
                ->active()
                ->latest()
                ->limit(6)
                ->get(['id','title','location','job_type','salary',
                       'company_id','urgent','remote_work','created_at']);
        });

        // الشركات المميزة — Cache 30 دقيقة
        $featuredCompanies = Cache::remember('home_featured_companies', 1800, function () {
            return Company::select('id','company_name','logo','industry','company_size')
                ->where('is_verified', true)
                ->where('status', 'active')
                ->withCount(['jobs' => fn($q) => $q->active()])
                ->orderByDesc('jobs_count')
                ->limit(6)
                ->get();
        });

        return view('home', compact('stats', 'latestJobs', 'featuredCompanies'));
    }

    public function search(Request $request)
    {
        return redirect()->route('jobs.index', $request->only(['keyword', 'location']));
    }
}
