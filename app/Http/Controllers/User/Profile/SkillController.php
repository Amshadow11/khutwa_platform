<?php

namespace App\Http\Controllers\User\Profile;

use App\Actions\Profile\RefreshProfessionalProfileAction;
use App\Actions\Profile\UpsertUserSkillAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreSkillRequest;
use App\Models\UserSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SkillController extends Controller
{
    public function store(StoreSkillRequest $request, UpsertUserSkillAction $action): RedirectResponse
    {
        $action->execute($request->user('web'), $request->validated());

        return back()->with('success', 'تمت إضافة المهارة.');
    }

    public function destroy(UserSkill $skill, RefreshProfessionalProfileAction $refreshProfile): RedirectResponse
    {
        Gate::authorize('delete', $skill);
        $user = request()->user('web');
        $skill->delete();
        $refreshProfile->execute($user->fresh());

        return back()->with('success', 'تم حذف المهارة.');
    }
}
