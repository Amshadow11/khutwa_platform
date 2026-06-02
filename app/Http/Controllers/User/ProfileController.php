<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Actions\Profile\RefreshProfessionalProfileAction;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // ========================================================
    // عرض الملف الشخصي
    // ========================================================

    /**
     * GET /user/profile
     */
    public function show(RefreshProfessionalProfileAction $refreshProfile): View
    {
        $user = Auth::guard('web')->user()->load([
            'professionalProfile',
            'canonicalSkills',
            'experiences',
            'educations',
            'projects',
            'certifications',
            'languages',
        ]);

        $completion = $refreshProfile->execute($user);

        return view('user.profile.show', compact('user', 'completion'));
    }

    // ========================================================
    // تعديل الملف الشخصي
    // ========================================================

    /**
     * GET /user/profile/edit
     */
    public function edit(): View
    {
        $user = Auth::guard('web')->user()->load([
            'professionalProfile',
            'canonicalSkills',
            'experiences',
            'educations',
            'projects',
            'certifications',
            'languages',
        ]);
        $profile = $user->professionalProfile()->firstOrCreate([]);
        $languages = Language::query()->where('is_active', true)->orderBy('name')->get();

        return view('user.profile.edit', compact('user', 'profile', 'languages'));
    }

    /**
     * PUT /user/profile
     */
    public function update(UpdateProfileRequest $request, RefreshProfessionalProfileAction $refreshProfile): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $data = $request->validated();

        // رفع الصورة الشخصية
        if ($request->hasFile('profile_picture')) {
            // حذف القديمة إذا وُجدت في storage
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // تحقق MIME حقيقي
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($request->file('profile_picture')->path());
            abort_if(
                ! in_array($realMime, ['image/jpeg', 'image/png', 'image/webp']),
                422, 'نوع الصورة غير مدعوم'
            );

            $data['profile_picture'] = $request->file('profile_picture')
                ->store('avatars', 'public');
        }

        // لا نسمح بتغيير كلمة المرور من هنا (لها endpoint منفصل)
        unset($data['password']);

        $user->update($data);
        $refreshProfile->execute($user->fresh());

        return redirect()
            ->route('user.profile.show')
            ->with('success', 'تم تحديث ملفك الشخصي بنجاح');
    }

    // ========================================================
    // تغيير كلمة المرور
    // ========================================================

    /**
     * PUT /user/profile/password
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        // التحقق من كلمة المرور الحالية
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        $user->update(['password' => $request->new_password]); // يُشفَّر بـ 'hashed' cast

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}
