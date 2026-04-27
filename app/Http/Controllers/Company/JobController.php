<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\StoreJobRequest;
use App\Http\Requests\Job\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobController extends Controller
{
    // ========================================================
    // قائمة الوظائف — للشركة
    // ========================================================

    /**
     * عرض قائمة وظائف الشركة مع إحصائياتها.
     * GET /company/jobs
     */
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

        return view('company.jobs.index', compact('jobs'));
    }

    // ========================================================
    // إنشاء وظيفة
    // ========================================================

    /**
     * عرض نموذج إنشاء وظيفة جديدة.
     * GET /company/jobs/create
     */
    public function create(): View
    {
        return view('company.jobs.create');
    }

    /**
     * حفظ الوظيفة الجديدة.
     * POST /company/jobs
     *
     * الـ Validation يحدث تلقائياً في StoreJobRequest قبل دخول هذه الدالة.
     */
    public function store(StoreJobRequest $request): RedirectResponse
    {
        $company = Auth::guard('company')->user();

        $company->jobs()->create($request->validated());

        return redirect()
            ->route('company.jobs.index')
            ->with('success', 'تم نشر الوظيفة بنجاح');
    }

    // ========================================================
    // عرض وظيفة + تفاصيل المتقدمين
    // ========================================================

    /**
     * عرض تفاصيل وظيفة + قائمة المتقدمين.
     * GET /company/jobs/{job}
     */
    public function show(Job $job): View
    {
        // التأكد أن الوظيفة تخص هذه الشركة
        $this->authorizeJob($job);

        // Eager Loading: نجلب المتقدمين مع بياناتهم دفعة واحدة — لا N+1
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

    /**
     * عرض نموذج التعديل.
     * GET /company/jobs/{job}/edit
     */
    public function edit(Job $job): View
    {
        $this->authorizeJob($job);

        return view('company.jobs.edit', compact('job'));
    }

    /**
     * حفظ التعديلات.
     * PUT /company/jobs/{job}
     */
    public function update(UpdateJobRequest $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $job->update($request->validated());

        return redirect()
            ->route('company.jobs.show', $job)
            ->with('success', 'تم تحديث الوظيفة بنجاح');
    }

    // ========================================================
    // حذف وظيفة (Soft Delete)
    // ========================================================

    /**
     * حذف الوظيفة (Soft Delete — يمكن استعادتها).
     * DELETE /company/jobs/{job}
     */
    public function destroy(Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $job->delete(); // Soft Delete فقط

        return redirect()
            ->route('company.jobs.index')
            ->with('success', 'تم حذف الوظيفة');
    }

    // ========================================================
    // تغيير حالة الوظيفة (تفعيل/تعطيل)
    // ========================================================

    /**
     * تبديل حالة الوظيفة بين نشط/معطّل.
     * PATCH /company/jobs/{job}/toggle
     */
    public function toggle(Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $newStatus  = $job->status === 'active' ? 'inactive' : 'active';
        $isActive   = $newStatus === 'active';

        $job->update([
            'status'    => $newStatus,
            'is_active' => $isActive,
        ]);

        $label = $isActive ? 'تم تفعيل الوظيفة' : 'تم إيقاف الوظيفة';

        return back()->with('success', $label);
    }

    // ========================================================
    // دالة مساعدة: التأكد من ملكية الوظيفة
    // ========================================================

    /**
     * يتحقق أن الوظيفة تخص الشركة المسجّلة دخولها.
     * يُوقف التنفيذ بـ 403 إذا لم تكن الوظيفة للشركة.
     */
    private function authorizeJob(Job $job): void
    {
        $companyId = Auth::guard('company')->id();

        abort_if(
            $job->company_id !== $companyId,
            403,
            'غير مصرح لك بالوصول إلى هذه الوظيفة'
        );
    }
}
