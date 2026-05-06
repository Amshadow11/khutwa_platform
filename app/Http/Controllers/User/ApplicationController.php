<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ApplicationReceived;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    // ========================================================
    // قائمة طلبات المستخدم
    // ========================================================

    /**
     * GET /user/applications
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();

        $applications = Application::with([
            'job:id,title,location,job_type,company_id,status,deadline',
            'job.company:id,company_name,logo',
        ])
            ->where('user_id', $user->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest('applied_at')
            ->paginate(12);

        // إحصائيات سريعة
        $rawStats = Application::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total'       => $rawStats->sum(),
            'pending'     => $rawStats->get('pending', 0),
            'shortlisted' => $rawStats->get('shortlisted', 0),
            'accepted'    => $rawStats->get('accepted', 0),
            'rejected'    => $rawStats->get('rejected', 0),
        ];
        return view('user.applications.index', compact('applications', 'stats'));
    }

    // ========================================================
    // التقديم على وظيفة
    // ========================================================

    /**
     * POST /jobs/{job}/apply
     *
     * Validation في StoreApplicationRequest قبل دخول الدالة.
     */
    public function store(StoreApplicationRequest $request, Job $job): RedirectResponse
    {
        // التحقق أن الوظيفة نشطة وغير منتهية
        abort_if(! $job->is_active || $job->status !== 'active', 404, 'هذه الوظيفة غير متاحة');
        abort_if($job->is_expired, 422, 'انتهى موعد التقديم على هذه الوظيفة');

        $user = Auth::guard('web')->user();

        // منع التقديم المزدوج
        $alreadyApplied = Application::where('job_id', $job->id)
                                     ->where('user_id', $user->id)
                                     ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'لقد تقدمت على هذه الوظيفة مسبقاً');
        }

        // رفع ملف CV إذا أُرسل
        $cvPath = null;
        if ($request->hasFile('cv')) {
            // التحقق الإضافي من MIME الفعلي (ليس الامتداد فقط)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($request->file('cv')->path());

            abort_if($realMime !== 'application/pdf', 422, 'الملف يجب أن يكون PDF حقيقي');

            $cvPath = $request->file('cv')->store(
                'cvs/' . date('Y/m'),
                'public'
            );
        }

        // إنشاء الطلب
        $application = Application::create([
            'job_id'          => $job->id,
            'user_id'         => $user->id,
            'cover_letter'    => $request->cover_letter,
            'cv_path'         => $cvPath,
            'applicant_name'  => $user->display_name,
            'applicant_email' => $user->email,
            'applicant_phone' => $user->phone,
            'status'          => Application::STATUS_PENDING,
            'applied_at'      => now(),
        ]);

        // إشعار الشركة باستلام طلب جديد
        $application->load('user', 'job');
        $job->company->notify(new ApplicationReceived($application));

        return redirect()
            ->route('user.applications.index')
            ->with('success', "تم إرسال طلبك على وظيفة \"{$job->title}\" بنجاح");
    }

    // ========================================================
    // تفاصيل طلب واحد
    // ========================================================

    /**
     * GET /user/applications/{application}
     */
    public function show(Application $application): View
    {
        $this->authorizeApplication($application);

        $application->load([
            'job.company',
            'statusHistory',
        ]);

        return view('user.applications.show', compact('application'));
    }

    // ========================================================
    // سحب الطلب (Withdraw)
    // ========================================================

    /**
     * DELETE /user/applications/{application}
     *
     * يسمح بالسحب فقط إذا كان الطلب في حالة pending أو viewed.
     */
    public function destroy(Application $application): RedirectResponse
    {
        $this->authorizeApplication($application);

        // لا يمكن سحب الطلب بعد مرحلة الاختصار
        if (in_array($application->status, ['shortlisted', 'interview', 'accepted', 'rejected'])) {
            return back()->with('error', 'لا يمكن سحب الطلب بعد وصوله لهذه المرحلة');
        }

        // حذف ملف CV من Storage إذا كان موجوداً ومخزناً محلياً
        if ($application->cv_path && Storage::disk('public')->exists($application->cv_path)) {
            Storage::disk('public')->delete($application->cv_path);
        }

        $application->delete(); // Soft Delete

        return back()->with('success', 'تم سحب طلبك بنجاح');
    }

    // ========================================================
    // Helper
    // ========================================================

    private function authorizeApplication(Application $application): void
    {
        abort_if(
            $application->user_id !== Auth::guard('web')->id(),
            403,
            'غير مصرح لك بالوصول لهذا الطلب'
        );
    }
}
