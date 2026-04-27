<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\View\View;
class JobsController extends Controller
{
    /**
     * صفحة الوظائف العامة مع البحث والفلترة.
     * GET /jobs
     *
     * يحل مشكلة SQL Injection الموجودة في jobs.php الأصلي.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'keyword', 'location', 'job_type',
            'category', 'experience_level',
            'remote_work', 'urgent',
        ]);

        $jobs = Job::with('company:id,company_name,logo')
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString(); // يحتفظ بالفلاتر في روابط الصفحات

        // قائمة المدن للفلتر
        $cities = ['صنعاء','عدن','تعز','الحديدة','إب','مأرب','حضرموت','لحج','أبين','ذمار','شبوة'];

        return view('jobs.index', compact('jobs', 'filters', 'cities'));
    }

    /**
     * تفاصيل وظيفة واحدة.
     * GET /jobs/{job}
     */
    public function show(Job $job): View
    {
        // لا نعرض الوظائف المعطّلة للعامة
        abort_if(! $job->is_active || $job->status !== 'active', 404);

        // زيادة المشاهدات
        $job->incrementViews();

        // تحميل بيانات الشركة
        $job->load('company');

        // هل المستخدم الحالي قدّم على هذه الوظيفة؟
        $hasApplied = false;
        if (auth('web')->check()) {
            $hasApplied = auth('web')->user()->hasAppliedTo($job->id);
        }

        // وظائف مشابهة من نفس الشركة أو نفس الموقع
        $relatedJobs = Job::with('company:id,company_name,logo')
            ->active()
            ->where('id', '!=', $job->id)
            ->where(function ($q) use ($job) {
                $q->where('company_id', $job->company_id)
                  ->orWhere('location', $job->location);
            })
            ->limit(4)
            ->get();

        return view('jobs.show', compact('job', 'hasApplied', 'relatedJobs'));
    }
}
