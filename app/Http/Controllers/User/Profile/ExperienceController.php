<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\DeleteProfessionalItemAction;
use App\Actions\Profile\StoreExperienceAction;
use App\Actions\Profile\UpdateExperienceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreExperienceRequest;
use App\Http\Requests\Profile\UpdateExperienceRequest;
use App\Models\UserExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExperienceController extends Controller
{
    public function store(StoreExperienceRequest $request, StoreExperienceAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة الخبرة العملية.');
    }

    public function update(
        UpdateExperienceRequest $request,
        UserExperience $experience,
        UpdateExperienceAction $action,
    ): RedirectResponse {
        Gate::authorize('update', $experience);
        $action->execute($experience, $request->validated());

        return back()->with('success', 'تم تحديث الخبرة العملية.');
    }

    public function destroy(
        UserExperience $experience,
        DeleteProfessionalItemAction $action,
    ): RedirectResponse {
        Gate::authorize('delete', $experience);
        $action->execute(request()->user('web'), $experience);

        return back()->with('success', 'تم حذف الخبرة العملية.');
    }
}
