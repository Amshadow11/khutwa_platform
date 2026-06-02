<?php

namespace App\Http\Controllers\Company;

use App\Actions\ATS\LogApplicationActivityAction;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Services\ATS\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $company = Auth::guard('company')->user();
        $jobIds = $company->jobs()->pluck('id');

        $applications = Application::with([
            'user:id,username,full_name,email,phone,profile_picture',
            'job:id,title,location',
            'resume:id,title,version_number',
        ])
            ->whereIn('job_id', $jobIds)
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->job_id, fn ($query, $jobId) => $query->where('job_id', $jobId))
            ->latest('applied_at')
            ->paginate(20);

        $stats = [
            'total' => Application::whereIn('job_id', $jobIds)->count(),
            'pending' => Application::whereIn('job_id', $jobIds)->where('status', 'pending')->count(),
            'shortlisted' => Application::whereIn('job_id', $jobIds)->where('status', 'shortlisted')->count(),
            'accepted' => Application::whereIn('job_id', $jobIds)->where('status', 'accepted')->count(),
        ];

        $jobs = $company->jobs()->select('id', 'title')->latest()->get();

        return view('company.applications.index', compact('applications', 'stats', 'jobs'));
    }

    public function pipeline(Request $request, ApplicationPipelineService $pipeline): View
    {
        $company = Auth::guard('company')->user();
        $jobs = $company->jobs()->select('id', 'title')->latest()->get();
        $columns = $pipeline->build($company, $request->only('job_id'));
        $stages = $pipeline->stages();

        return view('company.applications.pipeline', compact('columns', 'jobs', 'stages'));
    }

    public function show(Application $application): View
    {
        Gate::forUser(Auth::guard('company')->user())->authorize('view', $application);

        $application->markAsViewed();

        $application->load([
            'user',
            'resume:id,title,version_number,snapshot_hash,snapshot_created_at,generated_pdf_path',
            'job:id,title,location,job_type',
            'statusHistory',
            'atsNotes.company',
            'reviews.company',
            'interviews.company',
            'activities.company',
        ]);

        return view('company.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        Gate::forUser(Auth::guard('company')->user())->authorize('updateStatus', $application);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Application::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $application->updateStatus($validated['status'], $validated['note'] ?? null);
        app(LogApplicationActivityAction::class)->execute(
            $application,
            Auth::guard('company')->user(),
            'status_changed',
            'تم تغيير حالة الطلب إلى ' . $application->fresh()->status_label,
            ['status' => $validated['status']]
        );

        return back()->with('success', 'تم تحديث حالة الطلب إلى: ' . $application->fresh()->status_label);
    }
}
