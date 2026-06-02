<?php

namespace App\Http\Controllers\Company;

use App\Actions\ATS\AddApplicationNoteAction;
use App\Actions\ATS\ReviewApplicationAction;
use App\Actions\ATS\ScheduleInterviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ATS\ReviewApplicationRequest;
use App\Http\Requests\ATS\ScheduleInterviewRequest;
use App\Http\Requests\ATS\StoreApplicationNoteRequest;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;

class ATSController extends Controller
{
    public function storeNote(StoreApplicationNoteRequest $request, Application $application, AddApplicationNoteAction $action): RedirectResponse
    {
        $action->execute($application, $request->user('company'), $request->validated());

        return back()->with('success', 'تمت إضافة الملاحظة.');
    }

    public function review(ReviewApplicationRequest $request, Application $application, ReviewApplicationAction $action): RedirectResponse
    {
        $action->execute($application, $request->user('company'), $request->validated());

        return back()->with('success', 'تم حفظ تقييم المرشح.');
    }

    public function scheduleInterview(ScheduleInterviewRequest $request, Application $application, ScheduleInterviewAction $action): RedirectResponse
    {
        $action->execute($application, $request->user('company'), $request->validated());

        return back()->with('success', 'تمت جدولة المقابلة.');
    }
}
