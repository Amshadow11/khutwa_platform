<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\DeleteProfessionalItemAction;
use App\Actions\Profile\StoreEducationAction;
use App\Actions\Profile\UpdateEducationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreEducationRequest;
use App\Http\Requests\Profile\UpdateEducationRequest;
use App\Models\UserEducation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EducationController extends Controller
{
    public function store(StoreEducationRequest $request, StoreEducationAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة المؤهل التعليمي.');
    }

    public function update(
        UpdateEducationRequest $request,
        UserEducation $education,
        UpdateEducationAction $action,
    ): RedirectResponse {
        Gate::authorize('update', $education);
        $action->execute($education, $request->validated());

        return back()->with('success', 'تم تحديث المؤهل التعليمي.');
    }

    public function destroy(UserEducation $education, DeleteProfessionalItemAction $action): RedirectResponse
    {
        Gate::authorize('delete', $education);
        $action->execute(request()->user('web'), $education);

        return back()->with('success', 'تم حذف المؤهل التعليمي.');
    }
}
