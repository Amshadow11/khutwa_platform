<?php

namespace App\Http\Controllers\User;

use App\Actions\Resume\CreateResumeAction;
use App\Actions\Resume\RefreshResumeSnapshotAction;
use App\Actions\Resume\UpdateResumeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resume\StoreResumeRequest;
use App\Http\Requests\Resume\UpdateResumeRequest;
use App\Jobs\Resume\GenerateResumePdfJob;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Services\Resume\ResumeRenderer;
use App\Services\Resume\ResumeSnapshotDiffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function index(): View
    {
        $resumes = request()->user('web')->resumes()
            ->with('template')
            ->latest()
            ->paginate(12);

        return view('user.resumes.index', compact('resumes'));
    }

    public function create(): View
    {
        $templates = ResumeTemplate::query()->where('is_active', true)->orderBy('name')->get();

        return view('user.resumes.create', compact('templates'));
    }

    public function store(StoreResumeRequest $request, CreateResumeAction $action): RedirectResponse
    {
        $resume = $action->execute($request->user('web'), $request->validated());

        return redirect()->route('user.resumes.show', $resume)->with('success', 'تم إنشاء السيرة الذاتية.');
    }

    public function show(Resume $resume, ResumeRenderer $renderer): View
    {
        Gate::authorize('view', $resume);

        return view('user.resumes.show', [
            'resume' => $resume->load(['template', 'sections.items']),
            'renderedResume' => $renderer->view($resume),
        ]);
    }

    public function edit(Resume $resume, ResumeSnapshotDiffService $snapshotDiff): View
    {
        Gate::authorize('update', $resume);
        $resume->load(['template', 'sections', 'user']);
        $templates = ResumeTemplate::query()->where('is_active', true)->orderBy('name')->get();
        $diff = $snapshotDiff->compareWithCurrentProfile($resume);

        return view('user.resumes.edit', compact('resume', 'templates', 'diff'));
    }

    public function preview(Resume $resume, ResumeRenderer $renderer)
    {
        Gate::authorize('view', $resume);

        return response($renderer->html($resume->load(['template', 'sections.items'])));
    }

    public function update(UpdateResumeRequest $request, Resume $resume, UpdateResumeAction $action): RedirectResponse
    {
        $resume = $action->execute($resume, $request->validated());

        return redirect()->route('user.resumes.show', $resume)->with('success', 'تم تحديث إعدادات السيرة.');
    }

    public function refreshSnapshot(Resume $resume, RefreshResumeSnapshotAction $action): RedirectResponse
    {
        Gate::authorize('update', $resume);
        $action->execute($resume);

        return back()->with('success', 'تم إنشاء Snapshot جديد من ملفك المهني الحالي.');
    }

    public function generatePdf(Resume $resume): RedirectResponse
    {
        Gate::authorize('generatePdf', $resume);
        $resume->forceFill([
            'pdf_status' => 'queued',
            'pdf_error' => null,
        ])->save();

        GenerateResumePdfJob::dispatch($resume->id);

        return back()->with('success', 'تم إرسال السيرة إلى قائمة توليد PDF.');
    }

    public function download(Resume $resume)
    {
        Gate::authorize('view', $resume);
        abort_if(! $resume->generated_pdf_path, 404);

        return Storage::disk(config('resumes.disk', 'private'))->download(
            $resume->generated_pdf_path,
            str($resume->title)->slug()->append('.pdf')->toString()
        );
    }

    public function destroy(Resume $resume): RedirectResponse
    {
        Gate::authorize('delete', $resume);
        $resume->delete();

        return redirect()->route('user.resumes.index')->with('success', 'تم حذف السيرة.');
    }
}
