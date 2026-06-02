<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\DeleteProfessionalItemAction;
use App\Actions\Profile\StoreCertificationAction;
use App\Actions\Profile\UpdateCertificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreCertificationRequest;
use App\Http\Requests\Profile\UpdateCertificationRequest;
use App\Models\UserCertification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CertificationController extends Controller
{
    public function store(StoreCertificationRequest $request, StoreCertificationAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة الشهادة.');
    }

    public function update(
        UpdateCertificationRequest $request,
        UserCertification $certification,
        UpdateCertificationAction $action,
    ): RedirectResponse {
        Gate::authorize('update', $certification);
        $action->execute($certification, $request->validated());

        return back()->with('success', 'تم تحديث الشهادة.');
    }

    public function destroy(UserCertification $certification, DeleteProfessionalItemAction $action): RedirectResponse
    {
        Gate::authorize('delete', $certification);
        $action->execute(request()->user('web'), $certification);

        return back()->with('success', 'تم حذف الشهادة.');
    }
}
