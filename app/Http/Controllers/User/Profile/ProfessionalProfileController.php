<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\UpdateProfessionalProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfessionalProfileRequest;
use Illuminate\Http\RedirectResponse;

class ProfessionalProfileController extends Controller
{
    public function update(
        UpdateProfessionalProfileRequest $request,
        UpdateProfessionalProfileAction $action,
    ): RedirectResponse {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تم تحديث الهوية المهنية بنجاح.');
    }
}
