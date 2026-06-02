<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\DeleteProfessionalItemAction;
use App\Actions\Profile\StoreProjectAction;
use App\Actions\Profile\UpdateProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreProjectRequest;
use App\Http\Requests\Profile\UpdateProjectRequest;
use App\Models\UserProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request, StoreProjectAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة المشروع.');
    }

    public function update(UpdateProjectRequest $request, UserProject $project, UpdateProjectAction $action): RedirectResponse
    {
        Gate::authorize('update', $project);
        $action->execute($project, $request->validated());

        return back()->with('success', 'تم تحديث المشروع.');
    }

    public function destroy(UserProject $project, DeleteProfessionalItemAction $action): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $action->execute(request()->user('web'), $project);

        return back()->with('success', 'تم حذف المشروع.');
    }
}
