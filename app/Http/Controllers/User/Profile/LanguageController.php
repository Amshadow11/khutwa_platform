<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\RefreshProfessionalProfileAction;
use App\Actions\Profile\UpsertUserLanguageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreLanguageRequest;
use App\Models\UserLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class LanguageController extends Controller
{
    public function store(StoreLanguageRequest $request, UpsertUserLanguageAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة اللغة.');
    }

    public function destroy(UserLanguage $language, RefreshProfessionalProfileAction $refreshProfile): RedirectResponse
    {
        Gate::authorize('delete', $language);
        $user = request()->user('web');
        $language->delete();
        $refreshProfile->execute($user->fresh());

        return back()->with('success', 'تم حذف اللغة.');
    }
}
