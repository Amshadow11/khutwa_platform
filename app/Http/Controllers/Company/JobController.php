<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\StoreJobRequest;
use App\Http\Requests\Job\UpdateJobRequest;
use App\Models\Job;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    // ========================================================
    // قائمة الوظائف
    // ========================================================

    public function index(Request $request): View
    {
        $company = Auth::guard('company')->user();

        $jobs = $company->jobs()
            ->withCount([
                'applications',
                'applications as pending_count' => fn($q) => $q->where('status', 'pending'),
            ])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        // ملخص الاستهلاك للـ View
        $usageSummary = $this->subscriptionService->getUsageSummary($company);

        return view('company.jobs.index', compact('jobs', 'usageSummary'));
    }

    // ========================================================
    // إنشاء وظيفة
    // ========================================================

    public function create(): View
    {
        $company = Auth::guard('company')->user();

        // نمرر معلومات الخطة للـ View لإظهار/إخفاء خيارات featured/urgent
        $canFeatured = $this->subscriptionService->canPostFeatured($company);
        $canUrgent   = $this->subscriptionService->canPostUrgent($company);
        $canPost     = $this->subscriptionService->canPostJob($company);

        return view('company.jobs.create', compact('canFeatured', 'canUrgent', 'canPost'));
    }

    public function store(StoreJobRequest $request): RedirectResponse
    {
        $company = Auth::guard('company')->user();

        // فحص حد الوظائف الشهري — خط الدفاع الأخير
        if (! $this->subscriptionService->canPostJob($company)) {
            return redirect()
                ->route('company.jobs.create')
                ->with('error', 'لقد تجاوزت الحد الشهري لنشر الوظائف. يرجى الترقية إلى خطة أعلى.');
        }

        $job = $company->jobs()->create($request->validated());

        // تسجيل الاستهلاك
        $this->subscriptionService->incrementUsage($company, 'max_jobs_per_month');

        // تسجيل استهلاك featured إذا طُلب
        if ($job->featured) {
            $this->subscriptionService->incrementUsage($company, 'featured_jobs');
        }

        $this->clearJobCache($company->id);

        return redirect()
            ->route('company.jobs.index')
            ->with('success', 'تم نشر الوظيفة بنجاح');
    }

    // ========================================================
    // عرض وظيفة
    // ========================================================

    public function show(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load([
            'applications' => fn($q) => $q->with('user')->latest('applied_at'),
        ]);

        $applicationStats = [
            'total'       => $job->applications->count(),
            'pending'     => $job->applications->where('status', 'pending')->count(),
            'shortlisted' => $job->applications->where('status', 'shortlisted')->count(),
            'accepted'    => $job->applications->where('status', 'accepted')->count(),
            'rejected'    => $job->applications->where('status', 'rejected')->count(),
        ];

        return view('company.jobs.show', compact('job', 'applicationStats'));
    }

    // ========================================================
    // تعديل وظيفة
    // ========================================================

    public function edit(Job $job): View
    {
        $this->authorizeJob($job);

        $company     = Auth::guard('company')->user();
        $canFeatured = $this->subscriptionService->canPostFeatured($company);
        $canUrgent   = $this->subscriptionService->canPostUrgent($company);

        return view('company.jobs.edit', compact('job', 'canFeatured', 'canUrgent'));
    }

    public function update(UpdateJobRequest $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $wasFeatured = $job->featured;
        $job->update($request->validated());

        // تسجيل استهلاك featured إذا تغيّر من false → true
        if (! $wasFeatured && $job->fresh()->featured) {
            $company = Auth::guard('company')->user();
            $this->subscriptionService->incrementUsage($company, 'featured_jobs');
        }

        $this->clearJobCache($job->company_id);

        return redirect()
            ->route('company.jobs.show', $job)
            ->with('success', 'تم تحديث الوظيفة بنجاح');
    }

    // ========================================================
    // حذف وظيفة
    // ========================================================

    public function destroy(Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $companyId = $job->company_id;
        $job->delete();

        $this->clearJobCache($companyId);

        return redirect()
            ->route('company.jobs.index')
            ->with('success', 'تم حذف الوظيفة');
    }

    // ========================================================
    // تغيير حالة الوظيفة
    // ========================================================

    public function toggle(Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $newStatus = $job->status === 'active' ? 'inactive' : 'active';

        $job->update([
            'status'    => $newStatus,
            'is_active' => $newStatus === 'active',
        ]);

        $this->clearJobCache($job->company_id);

        $label = $newStatus === 'active' ? 'تم تفعيل الوظيفة' : 'تم إيقاف الوظيفة';

        return back()->with('success', $label);
    }

    // ========================================================
    // Helpers
    // ========================================================

    private function authorizeJob(Job $job): void
    {
        abort_if(
            $job->company_id !== Auth::guard('company')->id(),
            403,
            'غير مصرح لك بالوصول إلى هذه الوظيفة'
        );
    }

    private function clearJobCache(int $companyId): void
    {
        Cache::forget("company_dashboard_{$companyId}");
        Cache::forget('home_latest_jobs');
        Cache::forget('home_stats');
    }
}