<?php

namespace App\Http\Controllers\User;

use App\Actions\Applications\CaptureApplicationResumeSnapshotAction;
use App\Actions\ATS\LogApplicationActivityAction;
use App\Actions\Files\StorePrivateUploadAction;
use App\Events\UserAppliedToJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Job;
use App\Services\Files\PrivateFileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly StorePrivateUploadAction $storePrivateUpload,
        private readonly PrivateFileStorageService $files,
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();

        $applications = Application::with([
            'job:id,title,location,job_type,company_id,status,deadline',
            'job.company:id,company_name,logo',
            'resume:id,title,version_number',
        ])
            ->where('user_id', $user->id)
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('applied_at')
            ->paginate(12);

        $rawStats = Application::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total' => $rawStats->sum(),
            'pending' => $rawStats->get('pending', 0),
            'shortlisted' => $rawStats->get('shortlisted', 0),
            'accepted' => $rawStats->get('accepted', 0),
            'rejected' => $rawStats->get('rejected', 0),
        ];

        return view('user.applications.index', compact('applications', 'stats'));
    }

    public function store(
        StoreApplicationRequest $request,
        Job $job,
        CaptureApplicationResumeSnapshotAction $captureSnapshot
    ): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        Gate::forUser($user)->authorize('apply', $job);

        $alreadyApplied = Application::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'لقد تقدمت على هذه الوظيفة مسبقًا');
        }

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $storedCv = $this->storePrivateUpload->execute(
                $request->file('cv'),
                'cvs/' . date('Y/m'),
                config('files.allowed_cv_mimes')
            );
            $cvPath = $storedCv->path;
        }

        $selectedResume = null;
        if ($request->filled('resume_id')) {
            $selectedResume = $user->resumes()
                ->whereKey($request->integer('resume_id'))
                ->firstOrFail();
        }

        $resumeSnapshot = $captureSnapshot->execute($user, $selectedResume, $cvPath);

        $application = Application::create(array_merge([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'cover_letter' => $request->cover_letter,
            'cv_path' => $cvPath,
            'applicant_name' => $user->display_name,
            'applicant_email' => $user->email,
            'applicant_phone' => $user->phone,
            'status' => Application::STATUS_PENDING,
            'applied_at' => now(),
        ], $resumeSnapshot));

        UserAppliedToJob::dispatch($application);
        app(LogApplicationActivityAction::class)->execute(
            $application,
            null,
            'application_submitted',
            'تم إرسال طلب التقديم.',
        );

        return redirect()
            ->route('user.applications.index')
            ->with('success', "تم إرسال طلبك على وظيفة \"{$job->title}\" بنجاح");
    }

    public function show(Application $application): View
    {
        Gate::forUser(Auth::guard('web')->user())->authorize('view', $application);

        $application->load([
            'job.company',
            'resume:id,title,version_number',
            'statusHistory',
        ]);

        return view('user.applications.show', compact('application'));
    }

    public function destroy(Application $application): RedirectResponse
    {
        Gate::forUser(Auth::guard('web')->user())->authorize('withdraw', $application);

        $this->files->delete($application->cv_path);
        $application->delete();

        return back()->with('success', 'تم سحب طلبك بنجاح');
    }
}
