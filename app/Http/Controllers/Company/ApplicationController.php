<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    // ========================================================
    // قائمة الطلبات الواردة
    // ========================================================

    /**
     * جميع الطلبات الواردة على وظائف الشركة.
     * GET /company/applications
     */
    public function index(Request $request): View
    {
        $company = Auth::guard('company')->user();

        // جلب IDs وظائف الشركة
        $jobIds = $company->jobs()->pluck('id');

        // جلب الطلبات مع Eager Loading كامل — استعلام واحد بدلاً من N+1
        $applications = Application::with(['user', 'job:id,title,location'])
            ->whereIn('job_id', $jobIds)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->job_id, fn($q, $j) => $q->where('job_id', $j))
            ->latest('applied_at')
            ->paginate(20);

        // إحصائيات سريعة للـ dashboard
        $stats = [
            'total'       => Application::whereIn('job_id', $jobIds)->count(),
            'pending'     => Application::whereIn('job_id', $jobIds)->where('status', 'pending')->count(),
            'shortlisted' => Application::whereIn('job_id', $jobIds)->where('status', 'shortlisted')->count(),
            'accepted'    => Application::whereIn('job_id', $jobIds)->where('status', 'accepted')->count(),
        ];

        // قائمة الوظائف للفلتر
        $jobs = $company->jobs()->select('id', 'title')->latest()->get();

        return view('company.applications.index', compact('applications', 'stats', 'jobs'));
    }

    // ========================================================
    // عرض طلب واحد بالتفصيل
    // ========================================================

    /**
     * عرض تفاصيل طلب تقديم واحد.
     * GET /company/applications/{application}
     */
    public function show(Application $application): View
    {
        $this->authorizeApplication($application);

        // تمييز الطلب كـ "تمت المشاهدة" تلقائياً
        $application->markAsViewed();

        // Eager Loading: بيانات المستخدم + الوظيفة + التاريخ
        $application->load([
            'user',
            'job:id,title,location,job_type',
            'statusHistory',
        ]);

        return view('company.applications.show', compact('application'));
    }

    // ========================================================
    // تحديث حالة الطلب
    // ========================================================

    /**
     * تحديث حالة طلب التقديم.
     * PATCH /company/applications/{application}/status
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorizeApplication($application);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Application::STATUSES)],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        $application->updateStatus($validated['status'], $validated['note'] ?? null);

        return back()->with('success', 'تم تحديث حالة الطلب إلى: ' . $application->fresh()->status_label);
    }

    // ========================================================
    // دالة مساعدة: التأكد من ملكية الطلب
    // ========================================================

    /**
     * التحقق أن الطلب يخص وظيفة تابعة لهذه الشركة.
     */
    private function authorizeApplication(Application $application): void
    {
        $companyId = Auth::guard('company')->id();

        // نتحقق عبر العلاقة: الطلب → الوظيفة → الشركة
        abort_if(
            $application->job->company_id !== $companyId,
            403,
            'غير مصرح لك بالوصول إلى هذا الطلب'
        );
    }
}
