<?php

namespace App\Http\Controllers\Company;

use App\Actions\ATS\MarkApplicationAsViewedAction;
use App\Actions\ATS\TransitionApplicationStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ATS\TransitionApplicationStatusRequest;
use App\Models\Application;
use App\Services\ATS\ApplicationPipelineService;
use App\Services\ATS\ApplicationStatusWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request, ApplicationStatusWorkflowService $workflow): View
    {
        $company = Auth::guard('company')->user();
        $jobIds = $company->jobs()->pluck('id');

        $applications = Application::with([
            'user:id,username,full_name,email,phone,profile_picture',
            'job:id,title,location',
            'resume:id,title,version_number',
            'latestAiMatch',
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

        return view('company.applications.index', compact('applications', 'stats', 'jobs', 'workflow'));
    }

    public function pipeline(
        Request $request,
        ApplicationPipelineService $pipeline,
        ApplicationStatusWorkflowService $workflow
    ): View
    {
        $company = Auth::guard('company')->user();
        $jobs = $company->jobs()->select('id', 'title')->latest()->get();
        $columns = $pipeline->build($company, $request->only('job_id'));

        return view('company.applications.pipeline', compact('columns', 'jobs', 'workflow'));
    }

    public function show(
        Application $application,
        MarkApplicationAsViewedAction $markAsViewed,
        ApplicationStatusWorkflowService $workflow
    ): View
    {
        $company = Auth::guard('company')->user();
        Gate::forUser($company)->authorize('view', $application);

        $markAsViewed->execute($application, $company);

        $application->load([
            'user',
            'resume:id,title,version_number,snapshot_hash,snapshot_created_at,generated_pdf_path',
            'job:id,title,location,job_type',
            'statusHistory.actor',
            'atsNotes.company',
            'reviews.company',
            'latestAiMatch.run',
            'interviews.company',
            'activities.actor',
        ]);

        return view('company.applications.show', compact('application', 'workflow'));
    }

    public function transitionStatus(
        TransitionApplicationStatusRequest $request,
        Application $application,
        TransitionApplicationStatusAction $transition
    ): RedirectResponse
    {
        $validated = $request->validated();
        $transition->execute(
            application: $application,
            toStatus: $validated['status'],
            actor: $request->user('company'),
            note: $validated['note'] ?? null,
        );

        return back()->with('success', 'تم تحديث حالة الطلب إلى: ' . $application->fresh()->status_label);
    }
}
